<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class RoleController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private RoleService $roleService
    ) {
    }

    public function index(): JsonResponse
    {
        try {
            $roles = $this->roleService->getAll();
            
            // Map permissions to simple array for FE
            $roles->transform(function ($role) {
                $role->permissions = $role->rights->pluck('name');
                return $role;
            });

            return $this->successResponse($roles);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $role = $this->roleService->create($request->all());
            return $this->createdResponse($role, 'role_created');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $role = $this->roleService->update($id, $request->all());
            return $this->updatedResponse($role, 'role_updated');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->roleService->delete($id);
            return $this->deletedResponse('role_deleted');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function updatePermissions(int $id, Request $request): JsonResponse
    {
        try {
            $permissions = $request->input('permissions', []);
            $role = $this->roleService->syncPermissions($id, $permissions);
            
            $role->permissions = $role->rights->pluck('name');
            
            return $this->successResponse($role, 'permissions_updated');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
