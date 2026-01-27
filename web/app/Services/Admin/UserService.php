<?php

namespace App\Services\Admin;

use App\Exceptions\NotFoundException;
use App\Helpers\Helper;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserService
{
    private string $module = 'user';

    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Get all users with pagination and search
     *
     * @param  int  $perPage
     * @param  string|null  $search
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->userRepository->getAll($perPage, $search);
    }

    /**
     * Get user by ID
     *
     * @param  int  $id
     * @return array
     * @throws NotFoundException
     */
    public function getById(int $id): array
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new NotFoundException("User with ID {$id} not found");
        }

        return $user->toArray();
    }

    /**
     * Create a new user
     *
     * @param  array  $data
     * @return array
     */
    public function create(array $data): array
    {
        // Prepare data for creation
        $userData = $this->prepareUserData($data, 'create');
        
        // Extract role before creating user
        $role = $userData['role'] ?? null;
        unset($userData['role']);

        $user = $this->userRepository->create($userData);
        
        // Sync role if provided
        if ($role) {
            $this->syncUserRole($user, $role);
        }

        // Log activity
        $this->logActivity(getAction($this->module), [], $data);

        return $user->load('roles')->toArray();
    }
    /**
     * Update user
     *
     * @param  int  $id
     * @param  array  $data
     * @return array
     * @throws NotFoundException
     */
    public function update(int $id, array $data): array
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new NotFoundException("User with ID {$id} not found");
        }

        \Log::info('User update request', ['user_id' => $id, 'data' => $data]);

        $oldValue = $user->toArray();
        $userData = $this->prepareUserData($data, 'update');
        
        \Log::info('Prepared user data', ['userData' => $userData]);
        
        // Extract role before updating user
        $role = $userData['role'] ?? null;
        unset($userData['role']);

        $user = $this->userRepository->update($user, $userData);
        
        \Log::info('User updated', ['user' => $user->toArray()]);
        
        // Sync role if provided
        if ($role) {
            $this->syncUserRole($user, $role);
        }

        // Log activity
        $this->logActivity(getAction($this->module), [$user->id], $data, $oldValue);

        return $user->load('roles')->toArray();
    }

    /**
     * Delete user (soft delete)
     *
     * @param  int  $id
     * @return void
     * @throws NotFoundException
     */
    public function delete(int $id): void
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new NotFoundException("User with ID {$id} not found");
        }

        $oldValue = $user->toArray();

        $this->userRepository->delete($user);

        // Log activity
        $this->logActivity(getAction($this->module, 'destroy'), [$user->id], null, $oldValue);
    }

    /**
     * Prepare user data for create/update
     *
     * @param  array  $data
     * @param  string  $action
     * @return array
     */
    private function prepareUserData(array $data, string $action): array
    {
        $baseData = [];

        // Basic fields
        if (isset($data['name'])) {
            $baseData['name'] = $data['name'];
        }
        if (isset($data['email'])) {
            $baseData['email'] = $data['email'];
        }
        // Handle is_active properly - could be bool, string "true"/"false", or 0/1
        if (array_key_exists('is_active', $data)) {
            $value = $data['is_active'];
            if (is_bool($value)) {
                $baseData['active'] = $value;
            } else if (is_string($value)) {
                $baseData['active'] = !in_array(strtolower($value), ['false', '0', '', 'no']);
            } else {
                $baseData['active'] = (bool)$value;
            }
        }

        // Add password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $baseData['password'] = Hash::make($data['password']);
        }

        // Handle role assignment
        if (isset($data['role']) && !empty($data['role'])) {
            $baseData['role'] = $data['role'];
        }

        // Add audit fields
        $auditData = $this->getAuditData($action);

        return array_merge($baseData, $auditData);
    }

    /**
     * Sync user role
     *
     * @param  \App\Models\User  $user
     * @param  string  $roleName
     * @return void
     */
    private function syncUserRole($user, string $roleName): void
    {
        $role = \App\Models\Role::where('name', $roleName)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }
    }

    /**
     * Get audit fields based on action
     *
     * @param  string  $action
     * @return array
     */
    private function getAuditData(string $action): array
    {
        $userId = auth()->id() ?? 0;
        $timestamp = Carbon::now()->toDateTimeString();

        return match ($action) {
            'create' => [
                'created_at' => $timestamp,
                'created_by' => $userId,
            ],
            'update' => [
                'updated_at' => $timestamp,
                'updated_by' => $userId,
            ],
            default => [],
        };
    }

    /**
     * Log activity
     *
     * @param  string  $action
     * @param  array  $objAction
     * @param  array|null  $newValue
     * @param  array|null  $oldValue
     * @return void
     */
    private function logActivity(string $action, array $objAction, ?array $newValue = null, ?array $oldValue = null): void
    {
        $data_log = [
            'action' => $action,
            'obj_action' => json_encode($objAction),
        ];

        if ($newValue !== null) {
            $data_log['new_value'] = json_encode($newValue);
        }

        if ($oldValue !== null) {
            $data_log['old_value'] = json_encode($oldValue);
        }

        Helper::addLog($data_log);
    }
}

