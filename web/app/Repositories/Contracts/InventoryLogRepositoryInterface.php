<?php

namespace App\Repositories\Contracts;

use App\Models\InventoryLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryLogRepositoryInterface
{
    /**
     * Create inventory log
     *
     * @param  array  $data
     * @return InventoryLog
     */
    public function create(array $data): InventoryLog;

    /**
     * Get inventory logs by warehouse with pagination
     *
     * @param  int  $warehouseId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByWarehouse(int $warehouseId, int $perPage = 20): LengthAwarePaginator;
}


