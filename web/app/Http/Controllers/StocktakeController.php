<?php

namespace App\Http\Controllers;

use App\Http\Requests\StocktakeStoreRequest;
use App\Http\Requests\StocktakeItemUpdateRequest;
use App\Services\StocktakeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class StocktakeController extends Controller
{
    public function __construct(
        private StocktakeService $stocktakeService
    ) {
    }

    /**
     * Get all stocktakes
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['warehouse_id', 'status']);
            $perPage = $request->input('per_page', 15);

            $stocktakes = $this->stocktakeService->getAll($filters, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $stocktakes->items(),
                'meta' => [
                    'current_page' => $stocktakes->currentPage(),
                    'last_page' => $stocktakes->lastPage(),
                    'per_page' => $stocktakes->perPage(),
                    'total' => $stocktakes->total(),
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
     * Get single stocktake
     */
    public function show(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->getById($id);

            return response()->json([
                'status' => 'success',
                'data' => $stocktake,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create stocktake
     */
    public function store(StocktakeStoreRequest $request): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Tạo phiên kiểm kê thành công',
                'data' => $stocktake,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Start stocktake
     */
    public function start(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->start($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Bắt đầu kiểm kê, kho đã được khóa',
                'data' => $stocktake,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update item counts
     */
    public function updateItems(int $id, StocktakeItemUpdateRequest $request): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->updateItems($id, $request->validated()['items']);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật số lượng thành công',
                'data' => $stocktake,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Complete stocktake
     */
    public function complete(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->complete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Hoàn thành kiểm kê, chờ duyệt',
                'data' => $stocktake,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Approve stocktake
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->approve($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Duyệt và cân bằng kho thành công',
                'data' => $stocktake,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel stocktake
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->cancel($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Hủy kiểm kê thành công',
                'data' => $stocktake,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete stocktake
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->stocktakeService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa phiên kiểm kê thành công',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
