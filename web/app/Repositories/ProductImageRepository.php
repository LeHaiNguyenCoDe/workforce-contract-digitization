<?php

namespace App\Repositories;

use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;

class ProductImageRepository implements ProductImageRepositoryInterface
{
    /**
     * Create product image
     *
     * @param  array  $data
     * @return ProductImage
     */
    public function create(array $data): ProductImage
    {
        return ProductImage::create($data);
    }

    /**
     * Update all product images
     *
     * @param  int  $productId
     * @param  array  $data
     * @return int
     */
    public function updateByProduct(int $productId, array $data): int
    {
        return ProductImage::where('product_id', $productId)->update($data);
    }

    /**
     * Find product image by ID
     *
     * @param  int  $id
     * @return ProductImage|null
     */
    public function findById(int $id): ?ProductImage
    {
        return ProductImage::find($id);
    }

    /**
     * Delete product image
     *
     * @param  ProductImage  $image
     * @return bool
     */
    public function delete(ProductImage $image): bool
    {
        return $image->delete();
    }
}

