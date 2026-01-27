<?php

namespace App\Repositories;

use App\Models\Batch;
use App\Repositories\Contracts\BatchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BatchRepository implements BatchRepositoryInterface
{
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

    public function findById(int $id): ?Batch
    {
        return Batch::with(['product', 'warehouse', 'supplier', 'creator'])->find($id);
    }

    public function create(array $data): Batch
    {
        return Batch::create($data);
    }

    public function update(Batch $batch, array $data): Batch
    {
        $batch->update($data);
        return $batch->fresh(['product', 'warehouse', 'supplier']);
    }

    public function delete(int $id): bool
    {
        $batch = Batch::find($id);
        return $batch ? $batch->delete() : false;
    }

    public function getAvailable(int $productId, ?int $warehouseId = null): array
    {
        $query = Batch::where('product_id', $productId)
            ->available()
            ->fefo();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get()->toArray();
    }

    public function deductQuantity(Batch $batch, int $quantity): Batch
    {
        $batch->remaining_quantity -= $quantity;

        if ($batch->remaining_quantity == 0) {
            $batch->status = Batch::STATUS_DEPLETED;
        }

        $batch->save();
        return $batch;
    }
}
