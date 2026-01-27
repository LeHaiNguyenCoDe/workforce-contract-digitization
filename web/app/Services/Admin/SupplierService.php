<?php

namespace App\Services\Admin;

use App\Exceptions\NotFoundException;
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
        return $this->supplierRepository->getAll();
    }

    /**
     * Create supplier
     */
    public function create(array $data): Supplier
    {
        return $this->supplierRepository->create($data);
    }

    /**
     * Get supplier by ID
     * 
     * @throws NotFoundException
     */
    public function getById(int $id): Supplier
    {
        $supplier = $this->supplierRepository->findById($id);
        
        if (!$supplier) {
            throw new NotFoundException("Supplier with ID {$id} not found");
        }
        
        return $supplier;
    }

    /**
     * Update supplier
     * 
     * @throws NotFoundException
     */
    public function update(int $id, array $data): Supplier
    {
        $supplier = $this->supplierRepository->findById($id);
        
        if (!$supplier) {
            throw new NotFoundException("Supplier with ID {$id} not found");
        }
        
        return $this->supplierRepository->update($supplier, $data);
    }

    /**
     * Delete supplier
     * 
     * @throws NotFoundException
     */
    public function delete(int $id): bool
    {
        $supplier = $this->supplierRepository->findById($id);
        
        if (!$supplier) {
            throw new NotFoundException("Supplier with ID {$id} not found");
        }
        
        return $this->supplierRepository->delete($id);
    }
}
