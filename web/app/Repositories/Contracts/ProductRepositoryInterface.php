<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get all products with pagination and filters
     *
     * @param  int  $perPage
     * @param  string|null  $search
     * @param  int|null  $categoryId
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 12, ?string $search = null, ?int $categoryId = null): LengthAwarePaginator;

    /**
     * Get products by category
     *
     * @param  int  $categoryId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByCategory(int $categoryId, int $perPage = 12): LengthAwarePaginator;

    /**
     * Find product by ID
     *
     * @param  int  $id
     * @return Product|null
     */
    public function findById(int $id): ?Product;

    /**
     * Find product by slug
     *
     * @param  string  $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Create a new product
     *
     * @param  array  $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Update product
     *
     * @param  Product  $product
     * @param  array  $data
     * @return Product
     */
    public function update(Product $product, array $data): Product;

    /**
     * Delete product
     *
     * @param  Product  $product
     * @return bool
     */
    public function delete(Product $product): bool;
}

