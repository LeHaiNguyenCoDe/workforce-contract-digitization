<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthenticationException;
use App\Helpers\Helper;
use App\Helpers\LanguageHelper;
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
                'message' => LanguageHelper::apiMessage('login_success'),
                'data' => [
                    'user' => $result['user'],
                ],
            ]);

            // Laravel automatically sets the session cookie via Auth::login()
            // The cookie will be included in the Set-Cookie header automatically
            // No need to manually set it here as it's already handled by the session middleware

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
                'message' => LanguageHelper::apiMessage('error'),
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
                'message' => LanguageHelper::apiMessage('logout_success'),
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('error'),
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
                'message' => LanguageHelper::apiMessage('error'),
            ], 500);
        }
    }
}

