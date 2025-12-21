<?php

namespace App\Repositories\Contracts;

use App\Models\PromotionItem;

interface PromotionItemRepositoryInterface
{
    /**
     * Create promotion item
     *
     * @param  array  $data
     * @return PromotionItem
     */
    public function create(array $data): PromotionItem;

    /**
     * Find promotion item by ID
     *
     * @param  int  $id
     * @return PromotionItem|null
     */
    public function findById(int $id): ?PromotionItem;

    /**
     * Delete promotion item
     *
     * @param  PromotionItem  $item
     * @return bool
     */
    public function delete(PromotionItem $item): bool;
}

