<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReturnStoreRequest;
use App\Http\Requests\ReturnReceiveItemRequest;
use App\Services\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ReturnController extends Controller
{
    public function __construct(
        private ReturnService $returnService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'type']);
            $returns = $this->returnService->getAll($filters, $request->input('per_page', 15));

            return response()->json([
                'status' => 'success',
                'data' => $returns->items(),
                'meta' => [
                    'current_page' => $returns->currentPage(),
                    'last_page' => $returns->lastPage(),
                    'total' => $returns->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $return = $this->returnService->getById($id);
            return response()->json(['status' => 'success', 'data' => $return]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function store(ReturnStoreRequest $request): JsonResponse
    {
        try {
            $return = $this->returnService->create($request->validated());
            return response()->json(['status' => 'success', 'message' => 'Tạo yêu cầu RMA thành công', 'data' => $return], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $refundAmount = $request->input('refund_amount', 0);
            $return = $this->returnService->approve($id, $refundAmount);
            return response()->json(['status' => 'success', 'message' => 'Đã duyệt RMA', 'data' => $return]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $reason = $request->input('reason', '');
            $return = $this->returnService->reject($id, $reason);
            return response()->json(['status' => 'success', 'message' => 'Đã từ chối RMA', 'data' => $return]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function receiveItems(int $id, ReturnReceiveItemRequest $request): JsonResponse
    {
        try {
            $return = $this->returnService->receiveItems($id, $request->validated()['items']);
            return response()->json(['status' => 'success', 'message' => 'Đã cập nhật nhận hàng', 'data' => $return]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $return = $this->returnService->complete($id, $warehouseId);
            return response()->json(['status' => 'success', 'message' => 'Hoàn thành RMA và nhập kho', 'data' => $return]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $return = $this->returnService->cancel($id);
            return response()->json(['status' => 'success', 'message' => 'Đã hủy RMA', 'data' => $return]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
