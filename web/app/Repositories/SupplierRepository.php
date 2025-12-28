<?php

namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SupplierRepository implements SupplierRepositoryInterface
{
    public function getAll(): Collection
    {
        return Supplier::withCount('products')->get();
    }

    public function findById(int $id): ?Supplier
    {
        return Supplier::with('products')->find($id);
    }

    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);
        return $supplier->fresh();
    }

    public function delete(int $id): bool
    {
        $supplier = Supplier::find($id);
        return $supplier ? $supplier->delete() : false;
    }
}
