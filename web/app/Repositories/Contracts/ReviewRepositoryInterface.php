<?php

namespace App\Repositories\Contracts;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    /**
     * Get reviews by product ID
     *
     * @param  int  $productId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByProductId(int $productId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new review
     *
     * @param  array  $data
     * @return Review
     */
    public function create(array $data): Review;

    /**
     * Get all reviews with pagination
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator;

    /**
     * Find review by ID
     *
     * @param  int  $id
     * @return Review|null
     */
    public function findById(int $id): ?Review;
}

