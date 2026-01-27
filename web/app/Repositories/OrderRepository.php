<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Get all orders with pagination
     *
     * @param  int  $perPage
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Order::query()->with('items', 'user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('full_name', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($perPage);
    }

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

        return $query->with('items')->latest()->paginate($perPage);
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

