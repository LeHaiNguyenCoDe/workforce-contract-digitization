<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products with pagination and filters
     * For admin: Only show products that have stock in warehouse
     *
     * @param  int  $perPage
     * @param  string|null  $search
     * @param  int|null  $categoryId
     * @param  bool  $onlyWithStock  Only show products with stock > 0 (for admin)
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 12, ?string $search = null, ?int $categoryId = null, bool $onlyWithStock = false): LengthAwarePaginator
    {
        $query = Product::query()->with(['category', 'supplier']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // For admin: Only show products with stock > 0
        if ($onlyWithStock) {
            $query->whereIn('id', function($subquery) {
                $subquery->select('product_id')
                    ->from('stocks')
                    ->where('available_quantity', '>', 0)
                    ->groupBy('product_id')
                    ->havingRaw('SUM(available_quantity) > 0');
            });
        }

        // Add stock_quantity to each product after pagination
        $products = $query->select('id', 'category_id', 'supplier_id', 'name', 'slug', 'sku', 'price', 'thumbnail', 'short_description', 'min_stock_level', 'warehouse_type', 'storage_location')
            ->paginate($perPage);

        // Calculate stock_quantity for each product
        $productIds = $products->pluck('id')->toArray();
        if (!empty($productIds)) {
            $stockQuantities = \DB::table('stocks')
                ->select('product_id', \DB::raw('COALESCE(SUM(available_quantity), 0) as total_stock'))
                ->whereIn('product_id', $productIds)
                ->groupBy('product_id')
                ->pluck('total_stock', 'product_id')
                ->toArray();

            // Add stock_quantity to each product
            $products->getCollection()->transform(function($product) use ($stockQuantities) {
                $product->stock_quantity = $stockQuantities[$product->id] ?? 0;
                return $product;
            });
        } else {
            // If no products, set stock_quantity to 0
            $products->getCollection()->transform(function($product) {
                $product->stock_quantity = 0;
                return $product;
            });
        }

        return $products;
    }

    /**
     * Get products by category
     *
     * @param  int  $categoryId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByCategory(int $categoryId, int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->where('category_id', $categoryId)
            ->select('id', 'category_id', 'supplier_id', 'name', 'slug', 'sku', 'price', 'thumbnail', 'short_description', 'min_stock_level', 'warehouse_type', 'storage_location')
            ->paginate($perPage);
    }

    /**
     * Find product by ID
     *
     * @param  int  $id
     * @return Product|null
     */
    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    /**
     * Find product by slug
     *
     * @param  string  $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->first();
    }

    /**
     * Create a new product
     *
     * @param  array  $data
     * @return Product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update product
     *
     * @param  Product  $product
     * @param  array  $data
     * @return Product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Delete product
     *
     * @param  Product  $product
     * @return bool
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }
}

