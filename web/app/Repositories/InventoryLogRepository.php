<?php

namespace App\Repositories;

use App\Models\InventoryLog;
use App\Repositories\Contracts\InventoryLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InventoryLogRepository implements InventoryLogRepositoryInterface
{
    /**
     * Create inventory log
     *
     * @param  array  $data
     * @return InventoryLog
     */
    public function create(array $data): InventoryLog
    {
        return InventoryLog::create($data);
    }

    /**
     * Get inventory logs by warehouse with pagination
     *
     * @param  int  $warehouseId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByWarehouse(int $warehouseId, int $perPage = 20): LengthAwarePaginator
    {
        return InventoryLog::with(['product', 'productVariant', 'user', 'inboundBatch', 'qualityCheck'])
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

