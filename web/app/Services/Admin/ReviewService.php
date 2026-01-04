<?php

namespace App\Services\Admin;

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

        // Auto reply logic
        $this->createAutoReply($review);

        $review->load([
            'user:id,name',
            'replies' => function($q) {
                $q->with('user:id,name');
            }
        ]);

        return $review->toArray();
    }

    /**
     * Create auto reply based on rating
     *
     * @param \App\Models\Review $review
     * @return void
     */
    private function createAutoReply($review): void
    {
        $rating = (int) ($review->rating ?? 5);
        $content = '';

        if ($rating >= 3) {
            $content = 'Cảm ơn bạn đã đánh giá tốt cho sản phẩm của chúng tôi! Rất hân hạnh được phục vụ bạn.';
        } else {
            $content = 'Chúng tôi rất xin lỗi vì trải nghiệm không tốt của bạn. Shop sẽ cố gắng khắc phục để làm hài lòng quý khách trong những lần tới.';
        }

        // Find an admin user to respond
        $admin = \App\Models\User::whereHas('roles', function($q) {
            $q->where('name', 'Admin')->orWhere('name', 'Manager');
        })->first();

        // If no admin with roles found, try ID 1 or the first user
        if (!$admin) {
            $admin = \App\Models\User::find(1) ?? \App\Models\User::first();
        }

        if ($admin && !empty($content)) {
            $this->reviewRepository->create([
                'product_id' => $review->product_id,
                'user_id' => $admin->id,
                'rating' => 5,
                'content' => trim($content),
                'parent_id' => $review->id,
                'is_admin_reply' => true,
            ]);
        }
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

