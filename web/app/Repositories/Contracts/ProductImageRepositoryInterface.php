<?php

namespace App\Repositories\Contracts;

use App\Models\ProductImage;

interface ProductImageRepositoryInterface
{
    /**
     * Create product image
     *
     * @param  array  $data
     * @return ProductImage
     */
    public function create(array $data): ProductImage;

    /**
     * Update all product images
     *
     * @param  int  $productId
     * @param  array  $data
     * @return int
     */
    public function updateByProduct(int $productId, array $data): int;

    /**
     * Find product image by ID
     *
     * @param  int  $id
     * @return ProductImage|null
     */
    public function findById(int $id): ?ProductImage;

    /**
     * Delete product image
     *
     * @param  ProductImage  $image
     * @return bool
     */
    public function delete(ProductImage $image): bool;
}

