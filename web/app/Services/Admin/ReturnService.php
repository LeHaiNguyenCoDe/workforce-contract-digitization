<?php

namespace App\Services\Admin;

use App\Models\ReturnRequest;
use App\Models\ReturnItem;
use App\Models\Stock;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    /**
     * Get all returns
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ReturnRequest::query()
            ->with(['order:id,order_code', 'customer:id,name', 'creator:id,name'])
            ->withCount('items');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get by ID
     */
    public function getById(int $id): ReturnRequest
    {
        return ReturnRequest::with(['order', 'customer', 'items.product', 'creator'])->findOrFail($id);
    }

    /**
     * Create return request
     */
    public function create(array $data): ReturnRequest
    {
        $return = ReturnRequest::create([
            'return_code' => ReturnRequest::generateCode(),
            'order_id' => $data['order_id'],
            'customer_id' => $data['customer_id'] ?? null,
            'type' => $data['type'],
            'reason' => $data['reason'],
            'status' => ReturnRequest::STATUS_PENDING,
            'notes' => $data['notes'] ?? null,
            'requested_at' => now(),
            'created_by' => auth()->id(),
        ]);

        // Add items
        foreach ($data['items'] as $item) {
            ReturnItem::create([
                'return_id' => $return->id,
                'order_item_id' => $item['order_item_id'] ?? null,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'reason' => $item['reason'] ?? null,
            ]);
        }

        Helper::addLog(['action' => 'return.create', 'obj_action' => json_encode([$return->id])]);

        return $return->load('items.product');
    }

    /**
     * Approve return
     */
    public function approve(int $id, float $refundAmount = 0): ReturnRequest
    {
        $return = ReturnRequest::findOrFail($id);

        if ($return->status !== ReturnRequest::STATUS_PENDING) {
            throw new \Exception('Chỉ có thể duyệt yêu cầu đang chờ');
        }

        $return->status = ReturnRequest::STATUS_APPROVED;
        $return->approved_at = now();
        $return->approved_by = auth()->id();
        $return->refund_amount = $refundAmount;
        $return->save();

        Helper::addLog(['action' => 'return.approve', 'obj_action' => json_encode([$return->id])]);

        return $return;
    }

    /**
     * Reject return
     */
    public function reject(int $id, string $reason): ReturnRequest
    {
        $return = ReturnRequest::findOrFail($id);

        $return->status = ReturnRequest::STATUS_REJECTED;
        $return->notes = $reason;
        $return->save();

        Helper::addLog(['action' => 'return.reject', 'obj_action' => json_encode([$return->id])]);

        return $return;
    }

    /**
     * Receive items
     */
    public function receiveItems(int $id, array $items): ReturnRequest
    {
        $return = ReturnRequest::with('items')->findOrFail($id);

        if (!in_array($return->status, [ReturnRequest::STATUS_APPROVED, ReturnRequest::STATUS_RECEIVING])) {
            throw new \Exception('Không thể nhận hàng ở trạng thái này');
        }

        $return->status = ReturnRequest::STATUS_RECEIVING;

        foreach ($items as $itemData) {
            $item = ReturnItem::find($itemData['id']);
            if ($item && $item->return_id === $return->id) {
                $item->received_quantity = $itemData['received_quantity'];
                $item->condition = $itemData['condition'];
                $item->action = $itemData['action'] ?? ReturnItem::ACTION_RESTOCK;
                $item->save();
            }
        }

        // Check if all items received
        $allReceived = $return->items()->whereNull('received_quantity')->count() === 0;
        if ($allReceived) {
            $return->status = ReturnRequest::STATUS_RECEIVED;
        }

        $return->save();

        return $return->load('items.product');
    }

    /**
     * Complete return (restock items)
     */
    public function complete(int $id, ?int $warehouseId = null): ReturnRequest
    {
        $return = ReturnRequest::with('items')->findOrFail($id);

        if ($return->status !== ReturnRequest::STATUS_RECEIVED) {
            throw new \Exception('Chưa nhận đủ hàng');
        }

        DB::transaction(function () use ($return, $warehouseId) {
            foreach ($return->items as $item) {
                if ($item->action === ReturnItem::ACTION_RESTOCK && $item->received_quantity > 0) {
                    // Restock
                    $stock = Stock::firstOrCreate([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $warehouseId ?? 1,
                    ], ['quantity' => 0]);

                    $stock->quantity += $item->received_quantity;
                    $stock->save();

                    // Log
                    DB::table('inventory_logs')->insert([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $warehouseId ?? 1,
                        'type' => 'return_in',
                        'quantity' => $item->received_quantity,
                        'reference_type' => 'return',
                        'reference_id' => $return->id,
                        'notes' => "Nhập từ RMA {$return->return_code}",
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $return->status = ReturnRequest::STATUS_COMPLETED;
            $return->completed_at = now();
            $return->save();
        });

        Helper::addLog(['action' => 'return.complete', 'obj_action' => json_encode([$return->id])]);

        return $return;
    }

    /**
     * Cancel return
     */
    public function cancel(int $id): ReturnRequest
    {
        $return = ReturnRequest::findOrFail($id);

        if ($return->status === ReturnRequest::STATUS_COMPLETED) {
            throw new \Exception('Không thể hủy yêu cầu đã hoàn thành');
        }

        $return->status = ReturnRequest::STATUS_CANCELLED;
        $return->save();

        return $return;
    }
}
