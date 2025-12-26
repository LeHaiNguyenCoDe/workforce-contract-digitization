<?php

namespace App\Http\Controllers;

use App\Services\PurchaseRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class PurchaseRequestController extends Controller
{
    public function __construct(
        private PurchaseRequestService $purchaseRequestService
    ) {}

    /**
     * Get all purchase requests
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'source', 'warehouse_id', 'supplier_id']);
            $perPage = $request->input('per_page', 15);

            $requests = $this->purchaseRequestService->getAll($filters, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $requests->items(),
                'meta' => [
                    'current_page' => $requests->currentPage(),
                    'last_page' => $requests->lastPage(),
                    'per_page' => $requests->perPage(),
                    'total' => $requests->total(),
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
     * Get single purchase request
     */
    public function show(int $id): JsonResponse
    {
        try {
            $request = $this->purchaseRequestService->getById($id);

            return response()->json([
                'status' => 'success',
                'data' => $request,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create purchase request
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'requested_quantity' => 'required|integer|min:1',
                'current_stock' => 'nullable|integer|min:0',
                'min_stock' => 'nullable|integer|min:0',
                'notes' => 'nullable|string',
            ]);

            $pr = $this->purchaseRequestService->create($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Tạo phiếu đề nghị nhập hàng thành công',
                'data' => $pr,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update purchase request
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'nullable|exists:suppliers,id',
                'requested_quantity' => 'nullable|integer|min:1',
                'notes' => 'nullable|string',
            ]);

            $pr = $this->purchaseRequestService->update($id, $validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật phiếu đề nghị thành công',
                'data' => $pr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Approve purchase request
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->approve($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Phiếu đã được duyệt',
                'data' => $pr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject purchase request
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $reason = $request->input('reason');
            $pr = $this->purchaseRequestService->reject($id, $reason);

            return response()->json([
                'status' => 'success',
                'message' => 'Phiếu đã bị từ chối',
                'data' => $pr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Mark as ordered
     */
    public function markOrdered(int $id): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->markAsOrdered($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Đã chuyển trạng thái sang đã đặt hàng',
                'data' => $pr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Complete purchase request
     */
    public function complete(int $id): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->complete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Hoàn thành phiếu đề nghị',
                'data' => $pr,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel purchase request
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->cancel($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Đã hủy phiếu đề nghị',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete purchase request
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->purchaseRequestService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Đã xóa phiếu đề nghị',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get summary
     */
    public function summary(): JsonResponse
    {
        try {
            $summary = $this->purchaseRequestService->getSummary();

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
