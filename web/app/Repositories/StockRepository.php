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
        try {
            return Stock::with([
                'product:id,name,slug,price,description,category_id,supplier_id,min_stock_level,warehouse_type,storage_location',
                'product.category:id,name',
                'product.supplier:id,name',
                'productVariant:id,product_id,color,stock',
                'warehouse:id,name,code',
                'inboundBatch:id,batch_number,status',
                'qualityCheck:id,status,check_date'
            ])
            ->where('warehouse_id', $warehouseId)
            ->where('available_quantity', '>', 0) // BR-06.1: Chỉ hiển thị Available Inventory
            ->orderBy('updated_at', 'desc')
            ->get();
        } catch (\Exception $e) {
            \Log::error('Error getting stocks by warehouse: ' . $e->getMessage(), [
                'warehouse_id' => $warehouseId,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
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

