<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryAlertSettingRequest;
use App\Services\InventoryAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class InventoryAlertController extends Controller
{
    public function __construct(
        private InventoryAlertService $alertService
    ) {
    }

    /**
     * Get all inventory settings
     */
    public function settings(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['product_id', 'warehouse_id', 'search']);
            $perPage = $request->input('per_page', 15);

            $settings = $this->alertService->getAllSettings($filters, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $settings->items(),
                'meta' => [
                    'current_page' => $settings->currentPage(),
                    'last_page' => $settings->lastPage(),
                    'per_page' => $settings->perPage(),
                    'total' => $settings->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create or update inventory setting
     */
    public function saveSetting(InventoryAlertSettingRequest $request): JsonResponse
    {
        try {
            $setting = $this->alertService->saveSetting($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Lưu cài đặt tồn kho thành công',
                'data' => $setting->load(['product', 'warehouse']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete inventory setting
     */
    public function deleteSetting(int $id): JsonResponse
    {
        try {
            $this->alertService->deleteSetting($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa cài đặt thành công',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get all alerts
     */
    public function alerts(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $alerts = $this->alertService->getAlerts($warehouseId);

            return response()->json([
                'status' => 'success',
                'data' => $alerts,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get alert summary
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $summary = $this->alertService->getAlertSummary($warehouseId);

            return response()->json([
                'status' => 'success',
                'data' => $summary,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Trigger auto-create purchase requests
     */
    public function triggerAutoRequests(): JsonResponse
    {
        try {
            $created = $this->alertService->checkAndCreatePurchaseRequests();

            return response()->json([
                'status' => 'success',
                'message' => 'Đã kiểm tra và tạo ' . count($created) . ' phiếu đề nghị nhập hàng',
                'data' => ['created_count' => count($created)],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
