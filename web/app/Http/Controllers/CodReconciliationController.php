<?php

namespace App\Http\Controllers;

use App\Http\Requests\CodReconciliationStoreRequest;
use App\Http\Requests\CodReconciliationItemUpdateRequest;
use App\Services\CodReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CodReconciliationController extends Controller
{
    public function __construct(
        private CodReconciliationService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['shipping_partner', 'status']);
            $reconciliations = $this->service->getAll($filters, $request->input('per_page', 15));

            return response()->json([
                'status' => 'success',
                'data' => $reconciliations->items(),
                'meta' => [
                    'current_page' => $reconciliations->currentPage(),
                    'last_page' => $reconciliations->lastPage(),
                    'total' => $reconciliations->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $reconciliation = $this->service->getById($id);
            return response()->json(['status' => 'success', 'data' => $reconciliation]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function store(CodReconciliationStoreRequest $request): JsonResponse
    {
        try {
            $reconciliation = $this->service->create($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Tạo phiên đối soát thành công',
                'data' => $reconciliation
            ], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function updateItems(int $id, CodReconciliationItemUpdateRequest $request): JsonResponse
    {
        try {
            $reconciliation = $this->service->updateItems($id, $request->validated()['items']);
            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thành công',
                'data' => $reconciliation
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function reconcile(int $id): JsonResponse
    {
        try {
            $reconciliation = $this->service->reconcile($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Đối soát hoàn tất',
                'data' => $reconciliation
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return response()->json(['status' => 'success', 'message' => 'Đã xóa']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function shippingPartners(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->service->getShippingPartners()
        ]);
    }
}
