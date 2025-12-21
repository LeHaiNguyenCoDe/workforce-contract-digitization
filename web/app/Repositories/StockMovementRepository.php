<?php

namespace App\Repositories;

use App\Models\StockMovement;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    /**
     * Create stock movement
     *
     * @param  array  $data
     * @return StockMovement
     */
    public function create(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    /**
     * Get stock movements by warehouse with pagination
     *
     * @param  int  $warehouseId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByWarehouse(int $warehouseId, int $perPage = 20): LengthAwarePaginator
    {
        return StockMovement::with(['product:id,name,slug'])
            ->where('warehouse_id', $warehouseId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}

