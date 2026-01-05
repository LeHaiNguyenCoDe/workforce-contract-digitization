<?php

namespace App\Repositories;

use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * Get reviews by product ID
     *
     * @param  int  $productId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByProductId(int $productId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::query()
            ->where('product_id', $productId)
            ->whereNull('parent_id') // Get only top-level reviews
            ->with([
                'user:id,name',
                'replies' => function ($query) {
                    $query->with('user:id,name');
                }
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new review
     *
     * @param  array  $data
     * @return Review
     */
    public function create(array $data): Review
    {
        return Review::create($data);
    }

    /**
     * Get all reviews with pagination
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Review::query()
            ->with(['user:id,name,email', 'product:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find review by ID
     *
     * @param  int  $id
     * @return Review|null
     */
    public function findById(int $id): ?Review
    {
        return Review::with(['user:id,name,email', 'product:id,name'])->find($id);
    }

    /**
     * Get featured reviews (rating >= 4) with product info
     *
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedReviews(int $limit = 10)
    {
        return Review::query()
            ->where('rating', '>=', 4)
            ->whereNull('parent_id') // Only top-level reviews
            ->where('is_admin_reply', false) // Exclude admin replies
            ->with([
                'user:id,name',
                'product:id,name,slug,thumbnail',
                'product.images' => function ($query) {
                    $query->select('id', 'product_id', 'image_url', 'is_main')
                        ->orderByDesc('is_main')
                        ->limit(1);
                }
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }
}

