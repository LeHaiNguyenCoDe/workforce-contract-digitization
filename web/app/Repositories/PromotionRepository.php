<?php

namespace App\Repositories;

use App\Models\Promotion;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PromotionRepository implements PromotionRepositoryInterface
{
    /**
     * Get all promotions with pagination
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Promotion::query()
            ->with('items')
            ->orderByDesc('starts_at')
            ->paginate($perPage);
    }

    /**
     * Find promotion by ID
     *
     * @param  int  $id
     * @return Promotion|null
     */
    public function findById(int $id): ?Promotion
    {
        return Promotion::find($id);
    }

    /**
     * Find promotion by code
     *
     * @param  string  $code
     * @return Promotion|null
     */
    public function findByCode(string $code): ?Promotion
    {
        return Promotion::where('code', $code)->first();
    }

    /**
     * Create a new promotion
     *
     * @param  array  $data
     * @return Promotion
     */
    public function create(array $data): Promotion
    {
        return Promotion::create($data);
    }

    /**
     * Update promotion
     *
     * @param  Promotion  $promotion
     * @param  array  $data
     * @return Promotion
     */
    public function update(Promotion $promotion, array $data): Promotion
    {
        $promotion->update($data);
        return $promotion->fresh();
    }

    /**
     * Delete promotion
     *
     * @param  Promotion  $promotion
     * @return bool
     */
    public function delete(Promotion $promotion): bool
    {
        return $promotion->delete();
    }
}

