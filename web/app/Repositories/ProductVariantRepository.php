<?php

namespace App\Repositories;

use App\Models\ProductVariant;
use App\Repositories\Contracts\ProductVariantRepositoryInterface;

class ProductVariantRepository implements ProductVariantRepositoryInterface
{
    /**
     * Create product variant
     *
     * @param  array  $data
     * @return ProductVariant
     */
    public function create(array $data): ProductVariant
    {
        return ProductVariant::create($data);
    }

    /**
     * Find product variant by ID
     *
     * @param  int  $id
     * @return ProductVariant|null
     */
    public function findById(int $id): ?ProductVariant
    {
        return ProductVariant::find($id);
    }

    /**
     * Update product variant
     *
     * @param  ProductVariant  $variant
     * @param  array  $data
     * @return ProductVariant
     */
    public function update(ProductVariant $variant, array $data): ProductVariant
    {
        $variant->update($data);
        return $variant->fresh();
    }

    /**
     * Delete product variant
     *
     * @param  ProductVariant  $variant
     * @return bool
     */
    public function delete(ProductVariant $variant): bool
    {
        return $variant->delete();
    }
}

