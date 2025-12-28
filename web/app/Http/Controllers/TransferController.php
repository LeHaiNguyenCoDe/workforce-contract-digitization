<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferStoreRequest;
use App\Services\TransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class TransferController extends Controller
{
    public function __construct(
        private TransferService $transferService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['from_warehouse_id', 'to_warehouse_id', 'status']);
            $transfers = $this->transferService->getAll($filters, $request->input('per_page', 15));

            return response()->json([
                'status' => 'success',
                'data' => $transfers->items(),
                'meta' => [
                    'current_page' => $transfers->currentPage(),
                    'last_page' => $transfers->lastPage(),
                    'total' => $transfers->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->getById($id);
            return response()->json(['status' => 'success', 'data' => $transfer]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function store(TransferStoreRequest $request): JsonResponse
    {
        try {
            $transfer = $this->transferService->create($request->validated());
            return response()->json(['status' => 'success', 'message' => 'Tạo phiếu chuyển kho thành công', 'data' => $transfer], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function ship(int $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->ship($id);
            return response()->json(['status' => 'success', 'message' => 'Xuất kho thành công', 'data' => $transfer]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function receive(Request $request, int $id): JsonResponse
    {
        try {
            $receivedItems = $request->input('items', []);
            $transfer = $this->transferService->receive($id, $receivedItems);
            return response()->json(['status' => 'success', 'message' => 'Nhận kho thành công', 'data' => $transfer]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->cancel($id);
            return response()->json(['status' => 'success', 'message' => 'Đã hủy phiếu chuyển', 'data' => $transfer]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->transferService->delete($id);
            return response()->json(['status' => 'success', 'message' => 'Đã xóa']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
