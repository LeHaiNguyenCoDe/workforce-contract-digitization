<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewService
{
    public function __construct(
        private ReviewRepositoryInterface $reviewRepository,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get reviews by product ID
     *
     * @param  int  $productId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     * @throws NotFoundException
     */
    public function getByProductId(int $productId, int $perPage = 10): LengthAwarePaginator
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException("Product with ID {$productId} not found");
        }

        return $this->reviewRepository->getByProductId($productId, $perPage);
    }

    /**
     * Create review
     *
     * @param  int  $productId
     * @param  int  $userId
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function create(int $productId, int $userId, array $data): array
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new NotFoundException("Product with ID {$productId} not found");
        }

        $review = $this->reviewRepository->create([
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => $data['rating'],
            'content' => $data['content'] ?? null,
        ]);

        $review->load('user:id,name');

        return $review->toArray();
    }

    /**
     * Get all reviews
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviewRepository->getAll($perPage);
    }

    /**
     * Get review by ID
     *
     * @param  int  $id
     * @return array
     * @throws NotFoundException
     */
    public function getById(int $id): array
    {
        $review = $this->reviewRepository->findById($id);

        if (!$review) {
            throw new NotFoundException("Review with ID {$id} not found");
        }

        return $review->toArray();
    }
}

