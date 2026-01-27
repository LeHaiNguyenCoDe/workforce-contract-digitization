<?php

namespace App\Http\Controllers\Modules\Auth;

use App\Http\Controllers\Controller;

use App\Exceptions\AuthenticationException;
use App\Helpers\Helper;
use App\Http\Requests\Modules\Auth\AuthRequest;
use App\Services\Auth\AuthService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private AuthService $authService
    ) {
    }

    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $result = $this->authService->login(
                $credentials,
                $request->has('remember'),
                $request
            );

            return $this->successResponse([
                'user' => $result['user'],
            ], 'login_success');
        } catch (AuthenticationException $ex) {
            return $this->errorResponse($ex->getMessage(), null, $ex->getCode());
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function register(AuthRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('name', 'email', 'password', 'password_confirmation');
            $result = $this->authService->register($credentials);

            return $this->successResponse([
                'user' => $result['user'],
            ], 'register_success');
        } catch (AuthenticationException $ex) {
            return $this->errorResponse($ex->getMessage(), null, $ex->getCode());
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function logout(AuthRequest $request): JsonResponse
    {
        try {
            $this->authService->logout($request);

            return $this->successResponse(null, 'logout_success');
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function me(AuthRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->getCurrentUser();

            return $this->successResponse($user);
        } catch (AuthenticationException $ex) {
            // Return 200 but with null user to avoid red 401 logs in browser console
            return $this->successResponse(null, 'unauthenticated');
        } catch (\Exception $ex) {
            Helper::trackingError('auth', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }
}




