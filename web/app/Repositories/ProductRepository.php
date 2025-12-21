<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all products with pagination and filters
     *
     * @param  int  $perPage
     * @param  string|null  $search
     * @param  int|null  $categoryId
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 12, ?string $search = null, ?int $categoryId = null): LengthAwarePaginator
    {
        $query = Product::query()->with(['category']);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query
            ->select('id', 'category_id', 'name', 'slug', 'price', 'thumbnail', 'short_description')
            ->paginate($perPage);
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
            ->select('id', 'category_id', 'name', 'slug', 'price', 'thumbnail', 'short_description')
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

