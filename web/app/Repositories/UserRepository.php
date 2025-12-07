<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    /**
     * Get all users with pagination
     *
     * @param  int  $perPage
     * @param  string|null  $search
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = User::query()->select(['id', 'name', 'email', 'created_at']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Find user by ID
     *
     * @param  int  $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Find user by email
     *
     * @param  string  $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Create a new user
     *
     * @param  array  $data
     * @return User
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update user
     *
     * @param  User  $user
     * @param  array  $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete user (soft delete)
     *
     * @param  User  $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        $user->deleted_by = auth()->id() ?? 0;
        $user->active = false;
        $user->save();
        
        return $user->delete();
    }
}

