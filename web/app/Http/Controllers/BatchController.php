<?php

namespace App\Http\Controllers;

use App\Services\BatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class BatchController extends Controller
{
    public function __construct(
        private BatchService $batchService
    ) {}

    /**
     * Get all batches
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'product_id', 'warehouse_id', 'status', 'expiring_soon']);
            $perPage = $request->input('per_page', 15);

            $batches = $this->batchService->getAll($filters, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $batches->items(),
                'meta' => [
                    'current_page' => $batches->currentPage(),
                    'last_page' => $batches->lastPage(),
                    'per_page' => $batches->perPage(),
                    'total' => $batches->total(),
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
     * Get single batch
     */
    public function show(int $id): JsonResponse
    {
        try {
            $batch = $this->batchService->getById($id);

            return response()->json([
                'status' => 'success',
                'data' => $batch,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create batch
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'batch_code' => 'nullable|string|max:50|unique:batches,batch_code',
                'quantity' => 'required|integer|min:1',
                'unit_cost' => 'required|numeric|min:0',
                'manufacturing_date' => 'nullable|date',
                'expiry_date' => 'nullable|date|after:manufacturing_date',
                'notes' => 'nullable|string',
            ]);

            $batch = $this->batchService->create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Tạo lô hàng thành công',
                'data' => $batch,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update batch
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'quantity' => 'nullable|integer|min:0',
                'unit_cost' => 'nullable|numeric|min:0',
                'manufacturing_date' => 'nullable|date',
                'expiry_date' => 'nullable|date',
                'status' => 'nullable|in:available,reserved,expired,depleted',
                'notes' => 'nullable|string',
            ]);

            $batch = $this->batchService->update($id, $validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật lô hàng thành công',
                'data' => $batch,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete batch
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->batchService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa lô hàng thành công',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available batches for a product (FEFO order)
     */
    public function getProductBatches(Request $request, int $productId): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $batches = $this->batchService->getAvailableBatches($productId, $warehouseId);

            return response()->json([
                'status' => 'success',
                'data' => $batches,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get expiring soon summary
     */
    public function expiringSoon(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);
            $summary = $this->batchService->getExpiringSoonSummary($days);

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
}
