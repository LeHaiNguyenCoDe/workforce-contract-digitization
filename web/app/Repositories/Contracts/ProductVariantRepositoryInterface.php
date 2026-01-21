<?php

namespace App\Repositories\Contracts;

use App\Models\ProductVariant;

interface ProductVariantRepositoryInterface
{
    /**
     * Create product variant
     *
     * @param  array  $data
     * @return ProductVariant
     */
    public function create(array $data): ProductVariant;

    /**
     * Find product variant by ID
     *
     * @param  int  $id
     * @return ProductVariant|null
     */
    public function findById(int $id): ?ProductVariant;

    /**
     * Update product variant
     *
     * @param  ProductVariant  $variant
     * @param  array  $data
     * @return ProductVariant
     */
    public function update(ProductVariant $variant, array $data): ProductVariant;

    /**
     * Delete product variant
     *
     * @param  ProductVariant  $variant
     * @return bool
     */
    public function delete(ProductVariant $variant): bool;
    /**
     * Delete all variants for a product
     *
     * @param  int  $productId
     * @return void
     */
    public function deleteByProduct(int $productId): void;
}

