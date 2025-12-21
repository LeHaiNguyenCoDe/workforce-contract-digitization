<?php

namespace App\Repositories;

use App\Models\WishlistItem;
use App\Repositories\Contracts\WishlistRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository implements WishlistRepositoryInterface
{
    /**
     * Get wishlist items by user ID
     *
     * @param  int  $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection
    {
        return WishlistItem::with('product:id,name,price,thumbnail')
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Find wishlist item by user and product
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return WishlistItem|null
     */
    public function findByUserAndProduct(int $userId, int $productId): ?WishlistItem
    {
        return WishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    /**
     * Create wishlist item
     *
     * @param  array  $data
     * @return WishlistItem
     */
    public function create(array $data): WishlistItem
    {
        return WishlistItem::create($data);
    }

    /**
     * Delete wishlist item
     *
     * @param  WishlistItem  $item
     * @return bool
     */
    public function delete(WishlistItem $item): bool
    {
        return $item->delete();
    }

    /**
     * Delete wishlist item by user and product
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return bool
     */
    public function deleteByUserAndProduct(int $userId, int $productId): bool
    {
        return WishlistItem::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete() > 0;
    }
}

