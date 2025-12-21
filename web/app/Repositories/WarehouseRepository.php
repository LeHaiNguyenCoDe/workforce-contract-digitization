<?php

namespace App\Repositories;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    /**
     * Get all warehouses
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Warehouse::query()
            ->select('id', 'name', 'code', 'address', 'is_active')
            ->orderBy('name')
            ->get();
    }

    /**
     * Find warehouse by ID
     *
     * @param  int  $id
     * @return Warehouse|null
     */
    public function findById(int $id): ?Warehouse
    {
        return Warehouse::find($id);
    }

    /**
     * Create a new warehouse
     *
     * @param  array  $data
     * @return Warehouse
     */
    public function create(array $data): Warehouse
    {
        return Warehouse::create($data);
    }

    /**
     * Update warehouse
     *
     * @param  Warehouse  $warehouse
     * @param  array  $data
     * @return Warehouse
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);
        return $warehouse->fresh();
    }

    /**
     * Delete warehouse
     *
     * @param  Warehouse  $warehouse
     * @return bool
     */
    public function delete(Warehouse $warehouse): bool
    {
        return $warehouse->delete();
    }
}

