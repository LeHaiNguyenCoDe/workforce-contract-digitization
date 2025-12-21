<?php

namespace App\Repositories\Contracts;

use App\Models\Stock;
use Illuminate\Database\Eloquent\Collection;

interface StockRepositoryInterface
{
    /**
     * Find stock by warehouse, product and variant
     *
     * @param  int  $warehouseId
     * @param  int  $productId
     * @param  int|null  $productVariantId
     * @return Stock|null
     */
    public function findByWarehouseProduct(int $warehouseId, int $productId, ?int $productVariantId = null): ?Stock;

    /**
     * Get all stocks by warehouse
     *
     * @param  int  $warehouseId
     * @return Collection
     */
    public function getByWarehouse(int $warehouseId): Collection;

    /**
     * Create or update stock
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return Stock
     */
    public function firstOrCreate(array $attributes, array $values = []): Stock;

    /**
     * Update stock
     *
     * @param  Stock  $stock
     * @param  array  $data
     * @return Stock
     */
    public function update(Stock $stock, array $data): Stock;
}

