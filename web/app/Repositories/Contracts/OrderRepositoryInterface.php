<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * Get orders by user ID
     *
     * @param  int|null  $userId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByUserId(?int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Find order by ID
     *
     * @param  int  $id
     * @return Order|null
     */
    public function findById(int $id): ?Order;

    /**
     * Find order by code
     *
     * @param  string  $code
     * @return Order|null
     */
    public function findByCode(string $code): ?Order;

    /**
     * Create a new order
     *
     * @param  array  $data
     * @return Order
     */
    public function create(array $data): Order;

    /**
     * Update order
     *
     * @param  Order  $order
     * @param  array  $data
     * @return Order
     */
    public function update(Order $order, array $data): Order;
}

