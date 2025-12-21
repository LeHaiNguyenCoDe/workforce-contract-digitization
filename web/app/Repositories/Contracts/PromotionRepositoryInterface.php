<?php

namespace App\Repositories\Contracts;

use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PromotionRepositoryInterface
{
    /**
     * Get all promotions with pagination
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator;

    /**
     * Find promotion by ID
     *
     * @param  int  $id
     * @return Promotion|null
     */
    public function findById(int $id): ?Promotion;

    /**
     * Find promotion by code
     *
     * @param  string  $code
     * @return Promotion|null
     */
    public function findByCode(string $code): ?Promotion;

    /**
     * Create a new promotion
     *
     * @param  array  $data
     * @return Promotion
     */
    public function create(array $data): Promotion;

    /**
     * Update promotion
     *
     * @param  Promotion  $promotion
     * @param  array  $data
     * @return Promotion
     */
    public function update(Promotion $promotion, array $data): Promotion;

    /**
     * Delete promotion
     *
     * @param  Promotion  $promotion
     * @return bool
     */
    public function delete(Promotion $promotion): bool;
}

