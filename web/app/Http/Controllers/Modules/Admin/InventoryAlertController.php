<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Admin\InventoryAlertSettingRequest;
use App\Services\Admin\InventoryAlertService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class InventoryAlertController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private InventoryAlertService $alertService
    ) {
    }

    public function settings(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['product_id', 'warehouse_id', 'search']);
            $perPage = $request->input('per_page', 15);

            $settings = $this->alertService->getAllSettings($filters, $perPage);

            return $this->successResponse([
                'items' => $settings->items(),
                'meta' => [
                    'current_page' => $settings->currentPage(),
                    'last_page' => $settings->lastPage(),
                    'per_page' => $settings->perPage(),
                    'total' => $settings->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function saveSetting(InventoryAlertSettingRequest $request): JsonResponse
    {
        try {
            $setting = $this->alertService->saveSetting($request->validated());

            return $this->successResponse($setting->load(['product', 'warehouse']), 'setting_saved');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function deleteSetting(int $id): JsonResponse
    {
        try {
            $this->alertService->deleteSetting($id);

            return $this->deletedResponse('setting_deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function alerts(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $alerts = $this->alertService->getAlerts($warehouseId);

            return $this->successResponse($alerts);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function summary(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $summary = $this->alertService->getAlertSummary($warehouseId);

            return $this->successResponse($summary);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function triggerAutoRequests(): JsonResponse
    {
        try {
            $created = $this->alertService->checkAndCreatePurchaseRequests();

            return $this->successResponse(
                ['created_count' => count($created)],
                'auto_requests_created'
            );
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}



