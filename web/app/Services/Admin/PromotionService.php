<?php

namespace App\Services\Admin;

use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\PromotionRepositoryInterface;
use App\Repositories\Contracts\PromotionItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PromotionService
{
    public function __construct(
        private PromotionRepositoryInterface $promotionRepository,
        private PromotionItemRepositoryInterface $promotionItemRepository
    ) {
    }

    /**
     * Get all promotions
     *
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return $this->promotionRepository->getAll($perPage);
    }

    /**
     * Get promotion by ID
     *
     * @param  int  $id
     * @return array
     * @throws NotFoundException
     */
    public function getById(int $id): array
    {
        $promotion = $this->promotionRepository->findById($id);

        if (!$promotion) {
            throw new NotFoundException("Promotion with ID {$id} not found");
        }

        $promotion->load('items');

        return $promotion->toArray();
    }

    /**
     * Create promotion
     *
     * @param  array  $data
     * @return array
     */
    public function create(array $data): array
    {
        $promotion = $this->promotionRepository->create($data);
        return $promotion->toArray();
    }

    /**
     * Update promotion
     *
     * @param  int  $id
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function update(int $id, array $data): array
    {
        $promotion = $this->promotionRepository->findById($id);

        if (!$promotion) {
            throw new NotFoundException("Promotion with ID {$id} not found");
        }

        $promotion = $this->promotionRepository->update($promotion, $data);
        return $promotion->toArray();
    }

    /**
     * Delete promotion
     *
     * @param  int  $id
     * @return void
     * @throws NotFoundException
     */
    public function delete(int $id): void
    {
        $promotion = $this->promotionRepository->findById($id);

        if (!$promotion) {
            throw new NotFoundException("Promotion with ID {$id} not found");
        }

        $this->promotionRepository->delete($promotion);
    }

    /**
     * Add item to promotion
     *
     * @param  int  $promotionId
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function addItem(int $promotionId, array $data): array
    {
        $promotion = $this->promotionRepository->findById($promotionId);

        if (!$promotion) {
            throw new NotFoundException("Promotion with ID {$promotionId} not found");
        }

        if (!isset($data['product_id']) && !isset($data['category_id'])) {
            throw new \InvalidArgumentException("Either product_id or category_id is required");
        }

        $item = $this->promotionItemRepository->create([
            'promotion_id' => $promotionId,
            'product_id' => $data['product_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'min_qty' => $data['min_qty'],
        ]);

        return $item->toArray();
    }

    /**
     * Remove item from promotion
     *
     * @param  int  $promotionId
     * @param  int  $itemId
     * @return void
     * @throws NotFoundException
     */
    public function removeItem(int $promotionId, int $itemId): void
    {
        $promotion = $this->promotionRepository->findById($promotionId);

        if (!$promotion) {
            throw new NotFoundException("Promotion with ID {$promotionId} not found");
        }

        $item = $this->promotionItemRepository->findById($itemId);

        if (!$item || $item->promotion_id !== $promotionId) {
            throw new NotFoundException("Item not found or does not belong to this promotion");
        }

        $this->promotionItemRepository->delete($item);
    }
}

