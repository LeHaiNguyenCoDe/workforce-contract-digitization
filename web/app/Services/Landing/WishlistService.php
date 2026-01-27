<?php

namespace App\Services\Landing;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\WishlistRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class WishlistService
{
    public function __construct(
        private WishlistRepositoryInterface $wishlistRepository,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get wishlist items by user ID
     *
     * @param  int  $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->wishlistRepository->getByUserId($userId);
    }

    /**
     * Add product to wishlist
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return array
     * @throws NotFoundException
     */
    public function addItem(int $userId, int $productId): array
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException("Product with ID {$productId} not found");
        }

        $item = $this->wishlistRepository->create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return $item->toArray();
    }

    /**
     * Remove product from wishlist
     *
     * @param  int  $userId
     * @param  int  $productId
     * @return void
     */
    public function removeItem(int $userId, int $productId): void
    {
        $this->wishlistRepository->deleteByUserAndProduct($userId, $productId);
    }
}

