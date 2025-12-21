<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get orders by user ID
     *
     * @param  int|null  $userId
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getByUserId(?int $userId, int $perPage = 10): LengthAwarePaginator
    {
        $query = Order::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find order by ID
     *
     * @param  int  $id
     * @return Order|null
     */
    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    /**
     * Find order by code
     *
     * @param  string  $code
     * @return Order|null
     */
    public function findByCode(string $code): ?Order
    {
        return Order::where('code', $code)->first();
    }

    /**
     * Create a new order
     *
     * @param  array  $data
     * @return Order
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Update order
     *
     * @param  Order  $order
     * @param  array  $data
     * @return Order
     */
    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh();
    }
}

