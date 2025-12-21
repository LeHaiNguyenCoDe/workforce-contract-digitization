<?php

namespace App\Repositories;

use App\Models\Stock;
use App\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StockRepository implements StockRepositoryInterface
{
    /**
     * Find stock by warehouse, product and variant
     *
     * @param  int  $warehouseId
     * @param  int  $productId
     * @param  int|null  $productVariantId
     * @return Stock|null
     */
    public function findByWarehouseProduct(int $warehouseId, int $productId, ?int $productVariantId = null): ?Stock
    {
        return Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('product_variant_id', $productVariantId)
            ->first();
    }

    /**
     * Get all stocks by warehouse
     *
     * @param  int  $warehouseId
     * @return Collection
     */
    public function getByWarehouse(int $warehouseId): Collection
    {
        return Stock::with('product:id,name,slug')
            ->where('warehouse_id', $warehouseId)
            ->get();
    }

    /**
     * Create or update stock
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return Stock
     */
    public function firstOrCreate(array $attributes, array $values = []): Stock
    {
        return Stock::firstOrCreate($attributes, $values);
    }

    /**
     * Update stock
     *
     * @param  Stock  $stock
     * @param  array  $data
     * @return Stock
     */
    public function update(Stock $stock, array $data): Stock
    {
        $stock->update($data);
        return $stock->fresh();
    }
}

