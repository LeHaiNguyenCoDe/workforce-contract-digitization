<?php

namespace App\Repositories\Contracts;

use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection;

interface WishlistRepositoryInterface
{
    /**
     * Get wishlist items by user ID
     *
     * @param  int  $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Find wishlist item by user and product
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return WishlistItem|null
     */
    public function findByUserAndProduct(int $userId, int $productId): ?WishlistItem;

    /**
     * Create wishlist item
     *
     * @param  array  $data
     * @return WishlistItem
     */
    public function create(array $data): WishlistItem;

    /**
     * Delete wishlist item
     *
     * @param  WishlistItem  $item
     * @return bool
     */
    public function delete(WishlistItem $item): bool;

    /**
     * Delete wishlist item by user and product
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return bool
     */
    public function deleteByUserAndProduct(int $userId, int $productId): bool;
}

