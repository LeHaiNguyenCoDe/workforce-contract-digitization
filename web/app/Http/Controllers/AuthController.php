<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Helper;
use App\Http\Requests\AuthRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Login user and return session
     */
    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $result = $this->authService->login(
                $credentials,
                $request->has('remember'),
                $request
            );

            $response = response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $result['user'],
                ],
            ]);

            // Add session cookie if session is available
            if ($result['session_id']) {
                $response->withCookie(cookie('laravel_session', $result['session_id'], 120));
            }

            return $response;
        } catch (AuthenticationException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], $ex->getCode());
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
            $this->authService->logout($request);

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
            $user = $this->authService->getCurrentUser();

            return response()->json([
                'status' => 'success',
                'data' => $user,
            ]);
        } catch (AuthenticationException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], $ex->getCode());
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}

