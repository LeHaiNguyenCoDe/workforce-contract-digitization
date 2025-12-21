<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    /**
     * Find or create cart by user ID
     *
     * @param  int|null  $userId
     * @return Cart
     */
    public function findOrCreateByUserId(?int $userId): Cart
    {
        if (!$userId) {
            throw new \InvalidArgumentException('User ID is required');
        }

        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Find or create cart by session ID
     *
     * @param  string  $sessionId
     * @return Cart
     */
    public function findOrCreateBySessionId(string $sessionId): Cart
    {
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Find cart by user ID
     *
     * @param  int  $userId
     * @return Cart|null
     */
    public function findByUserId(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)->first();
    }

    /**
     * Find cart by session ID
     *
     * @param  string  $sessionId
     * @return Cart|null
     */
    public function findBySessionId(string $sessionId): ?Cart
    {
        return Cart::where('session_id', $sessionId)->first();
    }
}

