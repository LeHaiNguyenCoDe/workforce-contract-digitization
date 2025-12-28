<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use App\Repositories\Contracts\BatchRepositoryInterface;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchService
{
    protected string $module = 'batches';

    public function __construct(
        private BatchRepositoryInterface $batchRepository
    ) {
    }

    /**
     * Get all batches with pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Batch::query()
            ->with(['product:id,name,sku', 'warehouse:id,name', 'supplier:id,name'])
            ->select([
                'id',
                'batch_code',
                'product_id',
                'warehouse_id',
                'supplier_id',
                'quantity',
                'remaining_quantity',
                'unit_cost',
                'manufacturing_date',
                'expiry_date',
                'status',
                'created_at'
            ]);

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('batch_code', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['expiring_soon'])) {
            $query->expiringSoon((int) $filters['expiring_soon']);
        }

        // Default ordering: FEFO
        $query->fefo();

        return $query->paginate($perPage);
    }

    /**
     * Get batch by ID
     */
    public function getById(int $id): ?Batch
    {
        return Batch::with(['product', 'warehouse', 'supplier', 'creator'])
            ->findOrFail($id);
    }

    /**
     * Create new batch
     */
    public function create(array $data): Batch
    {
        $data['batch_code'] = $data['batch_code'] ?? Batch::generateBatchCode();
        $data['remaining_quantity'] = $data['quantity'];
        $data['created_by'] = auth()->id();

        $batch = Batch::create($data);

        // Update product cost (moving average)
        $this->updateProductCost($batch);

        // Log activity
        Helper::addLog([
            'action' => 'batch.create',
            'obj_action' => json_encode([$batch->id]),
            'new_value' => json_encode($data),
        ]);

        Log::info('Batch created', ['batch_id' => $batch->id, 'batch_code' => $batch->batch_code]);

        return $batch->load(['product', 'warehouse', 'supplier']);
    }

    /**
     * Update batch
     */
    public function update(int $id, array $data): Batch
    {
        $batch = Batch::findOrFail($id);
        $oldData = $batch->toArray();

        // If quantity changed, recalculate remaining
        if (isset($data['quantity']) && $data['quantity'] != $batch->quantity) {
            $diff = $data['quantity'] - $batch->quantity;
            $data['remaining_quantity'] = max(0, $batch->remaining_quantity + $diff);
        }

        $batch->update($data);

        // Log activity
        Helper::addLog([
            'action' => 'batch.update',
            'obj_action' => json_encode([$batch->id]),
            'old_value' => json_encode($oldData),
            'new_value' => json_encode($data),
        ]);

        return $batch->load(['product', 'warehouse', 'supplier']);
    }

    /**
     * Delete batch
     */
    public function delete(int $id): bool
    {
        $batch = Batch::findOrFail($id);

        if ($batch->remaining_quantity < $batch->quantity) {
            throw new \Exception('Không thể xóa lô hàng đã xuất một phần');
        }

        Helper::addLog([
            'action' => 'batch.delete',
            'obj_action' => json_encode([$batch->id]),
            'old_value' => json_encode($batch->toArray()),
        ]);

        return $batch->delete();
    }

    /**
     * Get available batches for a product (FEFO order)
     */
    public function getAvailableBatches(int $productId, ?int $warehouseId = null): array
    {
        $query = Batch::where('product_id', $productId)
            ->available()
            ->fefo();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get()->toArray();
    }

    /**
     * Allocate quantity from batches using FEFO
     */
    public function allocateQuantity(int $productId, int $quantity, ?int $warehouseId = null): array
    {
        $batches = Batch::where('product_id', $productId)
            ->available()
            ->fefo()
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        $allocated = [];
        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0)
                break;

            $allocateFromBatch = min($remaining, $batch->remaining_quantity);
            $allocated[] = [
                'batch_id' => $batch->id,
                'batch_code' => $batch->batch_code,
                'quantity' => $allocateFromBatch,
                'expiry_date' => $batch->expiry_date,
            ];
            $remaining -= $allocateFromBatch;
        }

        if ($remaining > 0) {
            throw new \Exception("Không đủ tồn kho. Còn thiếu {$remaining} sản phẩm.");
        }

        return $allocated;
    }

    /**
     * Deduct quantity from batch
     */
    public function deductFromBatch(int $batchId, int $quantity): Batch
    {
        $batch = Batch::findOrFail($batchId);

        if ($quantity > $batch->remaining_quantity) {
            throw new \Exception("Lô hàng {$batch->batch_code} không đủ số lượng");
        }

        $batch->remaining_quantity -= $quantity;

        if ($batch->remaining_quantity == 0) {
            $batch->status = Batch::STATUS_DEPLETED;
        }

        $batch->save();

        return $batch;
    }

    /**
     * Update product cost using moving average
     */
    protected function updateProductCost(Batch $batch): void
    {
        $product = Product::find($batch->product_id);
        if (!$product)
            return;

        // Get or create product cost record
        $costRecord = DB::table('product_costs')
            ->where('product_id', $batch->product_id)
            ->first();

        if (!$costRecord) {
            DB::table('product_costs')->insert([
                'product_id' => $batch->product_id,
                'average_cost' => $batch->unit_cost,
                'last_cost' => $batch->unit_cost,
                'total_quantity' => $batch->quantity,
                'total_value' => $batch->quantity * $batch->unit_cost,
                'last_updated_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Moving average calculation
            $newTotalQty = $costRecord->total_quantity + $batch->quantity;
            $newTotalValue = $costRecord->total_value + ($batch->quantity * $batch->unit_cost);
            $newAverageCost = $newTotalQty > 0 ? $newTotalValue / $newTotalQty : 0;

            DB::table('product_costs')
                ->where('product_id', $batch->product_id)
                ->update([
                    'average_cost' => round($newAverageCost, 2),
                    'last_cost' => $batch->unit_cost,
                    'total_quantity' => $newTotalQty,
                    'total_value' => $newTotalValue,
                    'last_updated_at' => now(),
                    'updated_at' => now(),
                ]);

            // Log cost history
            DB::table('product_cost_history')->insert([
                'product_id' => $batch->product_id,
                'batch_id' => $batch->id,
                'action' => 'inbound',
                'quantity' => $batch->quantity,
                'unit_cost' => $batch->unit_cost,
                'old_average_cost' => $costRecord->average_cost,
                'new_average_cost' => round($newAverageCost, 2),
                'reference_type' => 'batch',
                'reference_id' => $batch->id,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Mark expired batches
     */
    public function markExpiredBatches(): int
    {
        return Batch::expired()
            ->update(['status' => Batch::STATUS_EXPIRED]);
    }

    /**
     * Get expiring soon summary
     */
    public function getExpiringSoonSummary(int $days = 30): array
    {
        return Batch::expiringSoon($days)
            ->with('product:id,name,sku')
            ->get()
            ->groupBy('product_id')
            ->map(function ($batches) {
                return [
                    'product' => $batches->first()->product,
                    'total_quantity' => $batches->sum('remaining_quantity'),
                    'batches_count' => $batches->count(),
                    'earliest_expiry' => $batches->min('expiry_date'),
                ];
            })
            ->values()
            ->toArray();
    }
}
