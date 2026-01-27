<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get top N products for a category
     */
    public function getTopProductsByCategory(int $categoryId, int $limit = 4): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->with(['images' => function ($q) {
                $q->select('id', 'product_id', 'image_url', 'is_main')
                  ->orderByDesc('is_main');
            }])
            ->select('id', 'name', 'slug', 'price', 'thumbnail', 'category_id')
            ->limit($limit)
            ->get();
    }

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
    public function getAll(int $perPage = 12, array $filters = []): LengthAwarePaginator
    {
        $query = Product::query()->with(['category', 'supplier', 'variants']);

        // 1. Basic Filters
        if ($search = ($filters['search'] ?? null)) {
            $query->where('name', 'like', "%{$search}%");
        }

        // 2. Category Filter (Multiple or Single)
        if ($categoryIds = ($filters['category_ids'] ?? null)) {
            if (is_array($categoryIds)) {
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $categoryIds);
            }
        } elseif ($categoryId = ($filters['category_id'] ?? null)) {
            $query->where('category_id', $categoryId);
        }

        // 2.1 Active Category Filter (For Landing Page)
        if ($filters['active_category_only'] ?? false) {
            $query->whereHas('category', function($q) {
                $q->where('is_active', true);
            });
        }

        // 3. Brand Filter (Check brand column or specs->brand)
        if ($brands = ($filters['brands'] ?? null)) {
            $query->where(function ($q) use ($brands) {
                if (is_array($brands)) {
                    $q->whereIn('brand', $brands)
                        ->orWhere(function ($sq) use ($brands) {
                            foreach ($brands as $brand) {
                                $sq->orWhere('specs->brand', 'like', "%$brand%");
                            }
                        });
                } else {
                    $q->where('brand', $brands)
                        ->orWhere('specs->brand', 'like', "%$brands%");
                }
            });
        }

        // 4. Variant/Dimension Filters
        if (!empty($filters['color']) || !empty($filters['dimensions'])) {
            $query->where(function ($q) use ($filters) {
                // Check in variants
                $q->whereHas('variants', function ($vq) use ($filters) {
                    if ($color = ($filters['color'] ?? null)) {
                        $vq->where('color', $color);
                    }
                    if ($dimensions = ($filters['dimensions'] ?? null)) {
                        if (is_array($dimensions)) {
                            $vq->whereIn('dimension', $dimensions);
                        } else {
                            $vq->where('dimension', $dimensions);
                        }
                    }
                });

                // OR Check dimension in specs
                if ($dimensions = ($filters['dimensions'] ?? null)) {
                    $q->orWhere(function ($sq) use ($dimensions) {
                        if (is_array($dimensions)) {
                            foreach ($dimensions as $dim) {
                                $sq->orWhere('specs->dimension', 'like', "%$dim%");
                            }
                        } else {
                            $sq->where('specs->dimension', 'like', "%$dimensions%");
                        }
                    });
                }
            });
        }

        // 5. Sorting
        $sortBy = $filters['sort_by'] ?? 'latest';
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // 6. Admin Logic: Only show products with stock > 0
        if ($filters['only_with_stock'] ?? false) {
            $query->whereIn('id', function ($subquery) {
                $subquery->select('product_id')
                    ->from('stocks')
                    ->where('available_quantity', '>', 0)
                    ->groupBy('product_id')
                    ->havingRaw('SUM(available_quantity) > 0');
            });
        }

        // 7. Execution and Stock Calculation
        $products = $query->paginate($perPage);

        // Add stock_quantity to each product after pagination (Optimized)
        $productIds = $products->pluck('id')->toArray();
        if (!empty($productIds)) {
            $stockQuantities = \DB::table('stocks')
                ->select('product_id', \DB::raw('COALESCE(SUM(available_quantity), 0) as total_stock'))
                ->whereIn('product_id', $productIds)
                ->groupBy('product_id')
                ->pluck('total_stock', 'product_id')
                ->toArray();

            $products->getCollection()->transform(function ($product) use ($stockQuantities) {
                $product->stock_quantity = (int) ($stockQuantities[$product->id] ?? 0);
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

