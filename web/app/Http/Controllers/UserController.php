<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Helper;
use App\Helpers\LanguageHelper;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $req): JsonResponse
    {
        try {
            $perPage = $req->get('per_page', 15);
            $search = $req->get('search');

            $users = $this->userService->getAll($perPage, $search);

            return response()->json([
                'status' => 'success',
                'data' => $users,
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('error'),
            ], 500);
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $req): JsonResponse
    {
        try {
            $user = $this->userService->create($req->validated());

            return response()->json([
                'status' => 'success',
                'message' => LanguageHelper::apiMessage('user_created'),
                'data' => $user,
            ], 201);
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('error'),
            ], 500);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        try {
            if (!$user || !$user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => LanguageHelper::apiMessage('user_not_found'),
                ], 404);
            }

            $userData = $this->userService->getById($user->id);

            return response()->json([
                'status' => 'success',
                'data' => $userData,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('user_not_found'),
            ], 404);
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('error'),
            ], 500);
        }
    }

    /**
     * Update the specified user.
     */
    public function update(UserRequest $req, User $user): JsonResponse
    {
        try {
            $userData = $this->userService->update($user->id, $req->validated());

            return response()->json([
                'status' => 'success',
                'message' => LanguageHelper::apiMessage('user_updated'),
                'data' => $userData,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('user_not_found'),
            ], 404);
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('error'),
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->delete($user->id);

            return response()->json([
                'status' => 'success',
                'message' => LanguageHelper::apiMessage('user_deleted'),
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('user_not_found'),
            ], 404);
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('error'),
            ], 500);
        }
    }

    /**
     * Get user's orders
     */
    public function orders(User $user, Request $request): JsonResponse
    {
        try {
            if (!$user || !$user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => LanguageHelper::apiMessage('user_not_found'),
                ], 404);
            }

            $perPage = $request->get('per_page', 10);
            $orderService = app(\App\Services\OrderService::class);
            $orders = $orderService->getByUserId($user->id, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $orders,
            ]);
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => LanguageHelper::apiMessage('error'),
            ], 500);
        }
    }
}
