<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AutomationService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class AutomationController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private AutomationService $automationService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 20);
            $automations = $this->automationService->getAll($perPage);
            return $this->paginatedResponse($automations);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $automation = $this->automationService->create($request->all());
            return $this->createdResponse($automation, 'automation_created');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $automation = $this->automationService->update($id, $request->all());
            return $this->updatedResponse($automation, 'automation_updated');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->automationService->delete($id);
            return $this->deletedResponse('automation_deleted');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function toggle(int $id): JsonResponse
    {
        try {
            $automation = $this->automationService->toggleActive($id);
            return $this->successResponse($automation, 'automation_toggled');
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
