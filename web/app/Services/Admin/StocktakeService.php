<?php

namespace App\Services\Admin;

use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\Product;
use App\Models\Stock;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StocktakeService
{
    protected string $module = 'stocktakes';

    /**
     * Get all stocktakes with pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Stocktake::query()
            ->with(['warehouse:id,name', 'creator:id,name'])
            ->withCount('items');

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get stocktake by ID
     */
    public function getById(int $id): Stocktake
    {
        return Stocktake::with(['warehouse', 'creator', 'approver', 'items.product', 'items.batch'])
            ->findOrFail($id);
    }

    /**
     * Create new stocktake
     */
    public function create(array $data): Stocktake
    {
        $stocktake = Stocktake::create([
            'stocktake_code' => Stocktake::generateCode(),
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'status' => Stocktake::STATUS_DRAFT,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Auto-populate items from current stock
        $this->populateItems($stocktake);

        Helper::addLog([
            'action' => 'stocktake.create',
            'obj_action' => json_encode([$stocktake->id]),
        ]);

        return $stocktake->load(['items.product', 'warehouse']);
    }

    /**
     * Populate stocktake items from current stock
     */
    protected function populateItems(Stocktake $stocktake): void
    {
        $query = Stock::query()->where('quantity', '>', 0);
        
        if ($stocktake->warehouse_id) {
            $query->where('warehouse_id', $stocktake->warehouse_id);
        }

        $stocks = $query->get();

        foreach ($stocks as $stock) {
            StocktakeItem::create([
                'stocktake_id' => $stocktake->id,
                'product_id' => $stock->product_id,
                'batch_id' => $stock->batch_id ?? null,
                'system_quantity' => $stock->quantity,
                'actual_quantity' => null,
                'difference' => null,
            ]);
        }
    }

    /**
     * Start stocktake (lock warehouse)
     */
    public function start(int $id): Stocktake
    {
        $stocktake = Stocktake::findOrFail($id);

        if ($stocktake->status !== Stocktake::STATUS_DRAFT) {
            throw new \Exception('Chỉ có thể bắt đầu kiểm kê ở trạng thái Nháp');
        }

        $stocktake->start();

        Helper::addLog([
            'action' => 'stocktake.start',
            'obj_action' => json_encode([$stocktake->id]),
        ]);

        return $stocktake;
    }

    /**
     * Update item counts
     */
    public function updateItems(int $id, array $items): Stocktake
    {
        $stocktake = Stocktake::findOrFail($id);

        if ($stocktake->status !== Stocktake::STATUS_IN_PROGRESS) {
            throw new \Exception('Chỉ có thể cập nhật ở trạng thái Đang kiểm kê');
        }

        foreach ($items as $itemData) {
            $item = StocktakeItem::find($itemData['id']);
            if ($item && $item->stocktake_id === $stocktake->id) {
                $item->actual_quantity = $itemData['actual_quantity'];
                $item->calculateDifference();
                $item->reason = $itemData['reason'] ?? null;
                $item->save();
            }
        }

        return $stocktake->load(['items.product']);
    }

    /**
     * Complete stocktake (submit for approval)
     */
    public function complete(int $id): Stocktake
    {
        $stocktake = Stocktake::findOrFail($id);

        if ($stocktake->status !== Stocktake::STATUS_IN_PROGRESS) {
            throw new \Exception('Chỉ có thể hoàn thành ở trạng thái Đang kiểm kê');
        }

        // Check all items have been counted
        $uncounted = $stocktake->items()->whereNull('actual_quantity')->count();
        if ($uncounted > 0) {
            throw new \Exception("Còn {$uncounted} sản phẩm chưa kiểm đếm");
        }

        $stocktake->complete();

        Helper::addLog([
            'action' => 'stocktake.complete',
            'obj_action' => json_encode([$stocktake->id]),
        ]);

        return $stocktake;
    }

    /**
     * Approve stocktake and adjust stock
     */
    public function approve(int $id): Stocktake
    {
        $stocktake = Stocktake::with('items')->findOrFail($id);

        if ($stocktake->status !== Stocktake::STATUS_PENDING_APPROVAL) {
            throw new \Exception('Chỉ có thể duyệt ở trạng thái Chờ duyệt');
        }

        DB::transaction(function () use ($stocktake) {
            // Adjust stock for items with discrepancy
            foreach ($stocktake->items as $item) {
                if ($item->difference !== 0 && $item->difference !== null) {
                    $this->adjustStock($item, $stocktake);
                }
            }

            $stocktake->approve(auth()->id());
        });

        Helper::addLog([
            'action' => 'stocktake.approve',
            'obj_action' => json_encode([$stocktake->id]),
        ]);

        return $stocktake;
    }

    /**
     * Adjust stock based on stocktake item
     */
    protected function adjustStock(StocktakeItem $item, Stocktake $stocktake): void
    {
        $stock = Stock::where('product_id', $item->product_id)
            ->where('warehouse_id', $stocktake->warehouse_id)
            ->first();

        if ($stock) {
            $oldQty = $stock->quantity;
            $stock->quantity = $item->actual_quantity;
            $stock->save();

            // Log adjustment
            DB::table('inventory_logs')->insert([
                'product_id' => $item->product_id,
                'warehouse_id' => $stocktake->warehouse_id,
                'type' => 'adjustment',
                'quantity' => $item->difference,
                'before_quantity' => $oldQty,
                'after_quantity' => $item->actual_quantity,
                'reference_type' => 'stocktake',
                'reference_id' => $stocktake->id,
                'notes' => $item->reason ?? 'Điều chỉnh từ kiểm kê',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Cancel stocktake
     */
    public function cancel(int $id): Stocktake
    {
        $stocktake = Stocktake::findOrFail($id);

        if (in_array($stocktake->status, [Stocktake::STATUS_APPROVED, Stocktake::STATUS_CANCELLED])) {
            throw new \Exception('Không thể hủy phiên kiểm kê này');
        }

        $stocktake->cancel();

        Helper::addLog([
            'action' => 'stocktake.cancel',
            'obj_action' => json_encode([$stocktake->id]),
        ]);

        return $stocktake;
    }

    /**
     * Delete stocktake (only draft)
     */
    public function delete(int $id): bool
    {
        $stocktake = Stocktake::findOrFail($id);

        if ($stocktake->status !== Stocktake::STATUS_DRAFT) {
            throw new \Exception('Chỉ có thể xóa phiên kiểm kê ở trạng thái Nháp');
        }

        Helper::addLog([
            'action' => 'stocktake.delete',
            'obj_action' => json_encode([$stocktake->id]),
        ]);

        return $stocktake->delete();
    }
}
