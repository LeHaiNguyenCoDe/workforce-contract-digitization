<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\StockMovementRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WarehouseService
{
    public function __construct(
        private WarehouseRepositoryInterface $warehouseRepository,
        private StockRepositoryInterface $stockRepository,
        private StockMovementRepositoryInterface $stockMovementRepository
    ) {
    }

    /**
     * Get all warehouses
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->warehouseRepository->getAll();
    }

    /**
     * Get warehouse by ID
     *
     * @param  int  $id
     * @return array
     * @throws NotFoundException
     */
    public function getById(int $id): array
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$id} not found");
        }

        return $warehouse->toArray();
    }

    /**
     * Create warehouse
     *
     * @param  array  $data
     * @return array
     */
    public function create(array $data): array
    {
        $warehouse = $this->warehouseRepository->create($data);
        return $warehouse->toArray();
    }

    /**
     * Update warehouse
     *
     * @param  int  $id
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function update(int $id, array $data): array
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$id} not found");
        }

        $warehouse = $this->warehouseRepository->update($warehouse, $data);
        return $warehouse->toArray();
    }

    /**
     * Delete warehouse
     *
     * @param  int  $id
     * @return void
     * @throws NotFoundException
     */
    public function delete(int $id): void
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$id} not found");
        }

        $this->warehouseRepository->delete($warehouse);
    }

    /**
     * Get stocks by warehouse ID
     *
     * @param  int  $warehouseId
     * @return Collection
     * @throws NotFoundException
     */
    public function getStocks(int $warehouseId): Collection
    {
        $warehouse = $this->warehouseRepository->findById($warehouseId);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$warehouseId} not found");
        }

        return $this->stockRepository->getByWarehouse($warehouseId);
    }

    /**
     * Update stock for warehouse
     *
     * @param  int  $warehouseId
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function updateStock(int $warehouseId, array $data): array
    {
        $warehouse = $this->warehouseRepository->findById($warehouseId);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$warehouseId} not found");
        }

        // Find or create stock
        $stock = $this->stockRepository->firstOrCreate(
            [
                'warehouse_id' => $warehouseId,
                'product_id' => $data['product_id'],
                'product_variant_id' => $data['product_variant_id'] ?? null,
            ],
            ['quantity' => 0]
        );

        $oldQuantity = $stock->quantity;
        $newQuantity = max(0, $stock->quantity + $data['quantity']);
        
        $stock = $this->stockRepository->update($stock, ['quantity' => $newQuantity]);

        // Create stock movement record
        $this->stockMovementRepository->create([
            'warehouse_id' => $warehouseId,
            'product_id' => $data['product_id'],
            'product_variant_id' => $data['product_variant_id'] ?? null,
            'type' => $data['type'],
            'quantity' => abs($data['quantity']),
            'note' => $data['note'] ?? "Stock updated from {$oldQuantity} to {$newQuantity}",
        ]);

        return $stock->toArray();
    }

    /**
     * Get stock movements by warehouse
     *
     * @param  int  $warehouseId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     * @throws NotFoundException
     */
    public function getStockMovements(int $warehouseId, int $perPage = 20): LengthAwarePaginator
    {
        $warehouse = $this->warehouseRepository->findById($warehouseId);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$warehouseId} not found");
        }

        return $this->stockMovementRepository->getByWarehouse($warehouseId, $perPage);
    }
}

