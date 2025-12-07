<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Get all users with pagination
     *
     * @param  int  $perPage
     * @param  string|null  $search
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    /**
     * Find user by ID
     *
     * @param  int  $id
     * @return User|null
     */
    public function findById(int $id): ?User;

    /**
     * Find user by email
     *
     * @param  string  $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Update user
     *
     * @param  User  $user
     * @param  array  $data
     * @return User
     */
    public function update(User $user, array $data): User;

    /**
     * Delete user (soft delete)
     *
     * @param  User  $user
     * @return bool
     */
    public function delete(User $user): bool;
}

