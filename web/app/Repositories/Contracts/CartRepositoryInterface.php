<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;

interface CartRepositoryInterface
{
    /**
     * Find or create cart by user ID
     *
     * @param  int|null  $userId
     * @return Cart
     */
    public function findOrCreateByUserId(?int $userId): Cart;

    /**
     * Find or create cart by session ID
     *
     * @param  string  $sessionId
     * @return Cart
     */
    public function findOrCreateBySessionId(string $sessionId): Cart;

    /**
     * Find cart by user ID
     *
     * @param  int  $userId
     * @return Cart|null
     */
    public function findByUserId(int $userId): ?Cart;

    /**
     * Find cart by session ID
     *
     * @param  string  $sessionId
     * @return Cart|null
     */
    public function findBySessionId(string $sessionId): ?Cart;
}

