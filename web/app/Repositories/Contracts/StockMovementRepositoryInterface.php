<?php

namespace App\Repositories\Contracts;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface
{
    /**
     * Create stock movement
     *
     * @param  array  $data
     * @return StockMovement
     */
    public function create(array $data): StockMovement;

    /**
     * Get stock movements by warehouse with pagination
     *
     * @param  int  $warehouseId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByWarehouse(int $warehouseId, int $perPage = 20): LengthAwarePaginator;
}

