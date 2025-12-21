<?php

namespace App\Repositories\Contracts;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;

interface WarehouseRepositoryInterface
{
    /**
     * Get all warehouses
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Find warehouse by ID
     *
     * @param  int  $id
     * @return Warehouse|null
     */
    public function findById(int $id): ?Warehouse;

    /**
     * Create a new warehouse
     *
     * @param  array  $data
     * @return Warehouse
     */
    public function create(array $data): Warehouse;

    /**
     * Update warehouse
     *
     * @param  Warehouse  $warehouse
     * @param  array  $data
     * @return Warehouse
     */
    public function update(Warehouse $warehouse, array $data): Warehouse;

    /**
     * Delete warehouse
     *
     * @param  Warehouse  $warehouse
     * @return bool
     */
    public function delete(Warehouse $warehouse): bool;
}

