<?php

namespace App\Services\Admin;

use App\Models\InternalTransfer;
use App\Models\InternalTransferItem;
use App\Models\Stock;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransferService
{
    /**
     * Get all transfers with pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = InternalTransfer::query()
            ->with(['fromWarehouse:id,name', 'toWarehouse:id,name', 'creator:id,name'])
            ->withCount('items');

        if (!empty($filters['from_warehouse_id'])) {
            $query->where('from_warehouse_id', $filters['from_warehouse_id']);
        }

        if (!empty($filters['to_warehouse_id'])) {
            $query->where('to_warehouse_id', $filters['to_warehouse_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get transfer by ID
     */
    public function getById(int $id): InternalTransfer
    {
        return InternalTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product', 'items.batch'])
            ->findOrFail($id);
    }

    /**
     * Create transfer
     */
    public function create(array $data): InternalTransfer
    {
        if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
            throw new \Exception('Kho nguồn và kho đích không được trùng nhau');
        }

        $transfer = InternalTransfer::create([
            'transfer_code' => InternalTransfer::generateCode(),
            'from_warehouse_id' => $data['from_warehouse_id'],
            'to_warehouse_id' => $data['to_warehouse_id'],
            'status' => InternalTransfer::STATUS_DRAFT,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Add items
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                InternalTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        Helper::addLog([
            'action' => 'transfer.create',
            'obj_action' => json_encode([$transfer->id]),
        ]);

        return $transfer->load(['items.product', 'fromWarehouse', 'toWarehouse']);
    }

    /**
     * Ship transfer (deduct from source warehouse)
     */
    public function ship(int $id): InternalTransfer
    {
        $transfer = InternalTransfer::with('items')->findOrFail($id);

        if ($transfer->status !== InternalTransfer::STATUS_DRAFT && $transfer->status !== InternalTransfer::STATUS_PENDING) {
            throw new \Exception('Không thể xuất ở trạng thái này');
        }

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Deduct from source warehouse
                $stock = Stock::where('product_id', $item->product_id)
                    ->where('warehouse_id', $transfer->from_warehouse_id)
                    ->first();

                if (!$stock || $stock->quantity < $item->quantity) {
                    throw new \Exception("Sản phẩm ID {$item->product_id} không đủ tồn kho");
                }

                $stock->quantity -= $item->quantity;
                $stock->save();

                // Log
                DB::table('inventory_logs')->insert([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'type' => 'transfer_out',
                    'quantity' => -$item->quantity,
                    'before_quantity' => $stock->quantity + $item->quantity,
                    'after_quantity' => $stock->quantity,
                    'reference_type' => 'transfer',
                    'reference_id' => $transfer->id,
                    'notes' => "Chuyển đến {$transfer->toWarehouse->name}",
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $transfer->status = InternalTransfer::STATUS_IN_TRANSIT;
            $transfer->shipped_at = now();
            $transfer->shipped_by = auth()->id();
            $transfer->save();
        });

        Helper::addLog(['action' => 'transfer.ship', 'obj_action' => json_encode([$transfer->id])]);

        return $transfer;
    }

    /**
     * Receive transfer (add to destination warehouse)
     */
    public function receive(int $id, array $receivedItems = []): InternalTransfer
    {
        $transfer = InternalTransfer::with('items')->findOrFail($id);

        if ($transfer->status !== InternalTransfer::STATUS_IN_TRANSIT) {
            throw new \Exception('Không thể nhận ở trạng thái này');
        }

        DB::transaction(function () use ($transfer, $receivedItems) {
            foreach ($transfer->items as $item) {
                $receivedQty = $receivedItems[$item->id] ?? $item->quantity;
                $item->received_quantity = $receivedQty;
                $item->save();

                // Add to destination warehouse
                $stock = Stock::firstOrCreate([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                ], ['quantity' => 0]);

                $oldQty = $stock->quantity;
                $stock->quantity += $receivedQty;
                $stock->save();

                // Log
                DB::table('inventory_logs')->insert([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'type' => 'transfer_in',
                    'quantity' => $receivedQty,
                    'before_quantity' => $oldQty,
                    'after_quantity' => $stock->quantity,
                    'reference_type' => 'transfer',
                    'reference_id' => $transfer->id,
                    'notes' => "Nhận từ {$transfer->fromWarehouse->name}",
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $transfer->status = InternalTransfer::STATUS_RECEIVED;
            $transfer->received_at = now();
            $transfer->received_by = auth()->id();
            $transfer->save();
        });

        Helper::addLog(['action' => 'transfer.receive', 'obj_action' => json_encode([$transfer->id])]);

        return $transfer;
    }

    /**
     * Cancel transfer
     */
    public function cancel(int $id): InternalTransfer
    {
        $transfer = InternalTransfer::findOrFail($id);

        if ($transfer->status === InternalTransfer::STATUS_RECEIVED) {
            throw new \Exception('Không thể hủy phiếu đã nhận');
        }

        // If in transit, need to rollback stock
        if ($transfer->status === InternalTransfer::STATUS_IN_TRANSIT) {
            DB::transaction(function () use ($transfer) {
                foreach ($transfer->items as $item) {
                    $stock = Stock::where('product_id', $item->product_id)
                        ->where('warehouse_id', $transfer->from_warehouse_id)
                        ->first();
                    if ($stock) {
                        $stock->quantity += $item->quantity;
                        $stock->save();
                    }
                }
            });
        }

        $transfer->status = InternalTransfer::STATUS_CANCELLED;
        $transfer->save();

        Helper::addLog(['action' => 'transfer.cancel', 'obj_action' => json_encode([$transfer->id])]);

        return $transfer;
    }

    /**
     * Delete transfer (only draft)
     */
    public function delete(int $id): bool
    {
        $transfer = InternalTransfer::findOrFail($id);

        if ($transfer->status !== InternalTransfer::STATUS_DRAFT) {
            throw new \Exception('Chỉ có thể xóa phiếu nháp');
        }

        return $transfer->delete();
    }
}
