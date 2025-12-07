<?php

namespace App\Services;

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

        $user = $this->userRepository->create($userData);

        // Log activity
        $this->logActivity(getAction($this->module), [], $data);

        return $user->toArray();
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

        $oldValue = $user->toArray();
        $userData = $this->prepareUserData($data, 'update');

        $user = $this->userRepository->update($user, $userData);

        // Log activity
        $this->logActivity(getAction($this->module), [$user->id], $data, $oldValue);

        return $user->toArray();
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
        $baseData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        // Add password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $baseData['password'] = Hash::make($data['password']);
        }

        // Add audit fields
        $auditData = $this->getAuditData($action);

        return array_merge($baseData, $auditData);
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

