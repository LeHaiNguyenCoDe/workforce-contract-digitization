<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Exceptions\NotFoundException;
use App\Helpers\Helper;
use App\Helpers\LanguageHelper;
use App\Http\Requests\Modules\Admin\UserRequest;
use App\Models\User;
use App\Services\Admin\UserService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private UserService $userService
    ) {
    }

    public function index(Request $req): JsonResponse
    {
        try {
            $perPage = $req->get('per_page', 15);
            $search = $req->get('search');

            $users = $this->userService->getAll($perPage, $search);

            return $this->paginatedResponse($users);
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function store(UserRequest $req): JsonResponse
    {
        try {
            $user = $this->userService->create($req->validated());

            return $this->createdResponse($user, 'user_created');
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function show(User $user): JsonResponse
    {
        try {
            if (!$user || !$user->id) {
                return $this->notFoundResponse('user_not_found');
            }

            $userData = $this->userService->getById($user->id);

            return $this->successResponse($userData);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('user_not_found');
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function update(UserRequest $req, User $user): JsonResponse
    {
        try {
            $userData = $this->userService->update($user->id, $req->validated());

            return $this->updatedResponse($userData, 'user_updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('user_not_found');
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->delete($user->id);

            return $this->deletedResponse('user_deleted');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse('user_not_found');
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function orders(User $user, Request $request): JsonResponse
    {
        try {
            if (!$user || !$user->id) {
                return $this->notFoundResponse('user_not_found');
            }

            $perPage = $request->get('per_page', 10);
            $orderService = app(\App\Services\Admin\OrderService::class);
            $orders = $orderService->getByUserId($user->id, $perPage);

            return $this->paginatedResponse($orders);
        } catch (\Exception $ex) {
            Helper::trackingError('user', $ex->getMessage());
            return $this->serverErrorResponse('error', $ex);
        }
    }
}



