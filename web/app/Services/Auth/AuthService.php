<?php

namespace App\Services\Auth;

use App\Exceptions\AuthenticationException;
use App\Helpers\Helper;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Authenticate user and create session
     *
     * @param  array  $credentials
     * @param  bool  $remember
     * @param  Request  $request
     * @return array
     * @throws AuthenticationException
     */
    public function login(array $credentials, bool $remember, Request $request): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Check if user is active
        if (isset($user->active) && $user->active === false) {
            throw new AuthenticationException('Account is deactivated', 403);
        }

        // Check password
        if (!Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Login user (create session)
        Auth::login($user, $remember);

        // Log activity
        $this->logActivity('login', $user->id, ['email' => $user->email]);

        // Regenerate session ID for security
        $sessionId = null;
        if ($request->hasSession()) {
            $request->session()->regenerate();
            $sessionId = $request->session()->getId();
        }

        // Load roles relationship
        $user->load('roles');

        return [
            'user' => $this->formatUser($user),
            'session_id' => $sessionId,
        ];
    }

    /**
     * Logout user
     *
     * @param  Request  $request
     * @return void
     */
    public function logout(Request $request): void
    {
        $user = Auth::user();

        if ($user) {
            $this->logActivity('logout', $user->id);
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    /**
     * Get current authenticated user
     *
     * @return array
     * @throws AuthenticationException
     */
    public function getCurrentUser(): array
    {
        $user = Auth::user();

        if (!$user) {
            throw new AuthenticationException('Unauthenticated');
        }

        // Load roles relationship
        $user->load('roles');

        return $this->formatUser($user);
    }

    /**
     * Format user data for API response
     *
     * @param  mixed  $user
     * @return array
     */
    private function formatUser($user): array
    {
        // Load roles with their rights
        $user->load('roles.rights');

        // Collect all permissions from user's roles
        $permissions = collect();
        if ($user->roles) {
            foreach ($user->roles as $role) {
                if ($role->rights) {
                    foreach ($role->rights as $right) {
                        $permissions->push($right->name);
                    }
                }
            }
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'active' => $user->active ?? true,
            'language' => $user->language ?? 'vi',
            'roles' => $user->roles ? $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            })->toArray() : [],
            'permissions' => $permissions->unique()->values()->toArray(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * Log activity
     *
     * @param  string  $action
     * @param  int  $userId
     * @param  array|null  $data
     * @return void
     */
    private function logActivity(string $action, int $userId, ?array $data = null): void
    {
        $data_log = [
            'action' => $action,
            'obj_action' => json_encode([$userId]),
        ];

        if ($data) {
            $data_log['new_value'] = json_encode($data);
        }

        Helper::addLog($data_log);
    }
}
