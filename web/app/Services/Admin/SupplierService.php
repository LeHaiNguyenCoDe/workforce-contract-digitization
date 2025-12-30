<?php

namespace App\Services\Admin;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SupplierService
{
    public function __construct(
        private SupplierRepositoryInterface $supplierRepository
    ) {
    }

    /**
     * Get all suppliers
     */
    public function getAll(): Collection
    {
        return Supplier::withCount('products')->get();
    }

    /**
     * Create supplier
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Get supplier by ID
     */
    public function getById(int $id): ?Supplier
    {
        return Supplier::with('products')->find($id);
    }

    /**
     * Update supplier
     */
    public function update(int $id, array $data): Supplier
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($data);
        return $supplier;
    }

    /**
     * Delete supplier
     */
    public function delete(int $id): bool
    {
        $supplier = Supplier::findOrFail($id);
        return $supplier->delete();
    }
}
