<?php

namespace App\Repositories;

use App\Models\PromotionItem;
use App\Repositories\Contracts\PromotionItemRepositoryInterface;

class PromotionItemRepository implements PromotionItemRepositoryInterface
{
    /**
     * Create promotion item
     *
     * @param  array  $data
     * @return PromotionItem
     */
    public function create(array $data): PromotionItem
    {
        return PromotionItem::create($data);
    }

    /**
     * Find promotion item by ID
     *
     * @param  int  $id
     * @return PromotionItem|null
     */
    public function findById(int $id): ?PromotionItem
    {
        return PromotionItem::find($id);
    }

    /**
     * Delete promotion item
     *
     * @param  PromotionItem  $item
     * @return bool
     */
    public function delete(PromotionItem $item): bool
    {
        return $item->delete();
    }
}

