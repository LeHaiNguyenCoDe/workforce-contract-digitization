<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\AuthRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login user and return session
     */
    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // Check if user is active
            if (isset($user->active) && $user->active === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account is deactivated',
                ], 403);
            }

            // Check password
            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // Login user (create session)
            Auth::login($user, $request->has('remember'));

            $data_log['action'] = 'login';
            $data_log['obj_action'] = json_encode(array($user->id));
            $data_log['new_value'] = json_encode(array('email' => $user->email));
            Helper::addLog($data_log);

            // Regenerate session ID for security
            if ($request->hasSession()) {
                $request->session()->regenerate();
                $sessionId = $request->session()->getId();
            } else {
                $sessionId = null;
            }

            $response = response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ],
            ]);

            // Add session cookie if session is available
            if ($sessionId) {
                $response->withCookie(cookie('laravel_session', $sessionId, 120));
            }

            return $response;
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(AuthRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if ($user) {
                $data_log['action'] = 'logout';
                $data_log['obj_action'] = json_encode(array($user->id));
                Helper::addLog($data_log);
            }

            Auth::logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(AuthRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated',
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                ],
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}

