<?php

namespace App\Http\Controllers;

use App\Services\CodReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CodReconciliationController extends Controller
{
    public function __construct(
        private CodReconciliationService $service
    ) {}

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

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'shipping_partner' => 'required|string|max:50',
                'period_from' => 'required|date',
                'period_to' => 'required|date|after_or_equal:period_from',
                'notes' => 'nullable|string',
            ]);

            $reconciliation = $this->service->create($validated);
            return response()->json([
                'status' => 'success',
                'message' => 'Tạo phiên đối soát thành công',
                'data' => $reconciliation
            ], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function updateItems(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|exists:cod_reconciliation_items,id',
                'items.*.received_amount' => 'required|numeric|min:0',
                'items.*.notes' => 'nullable|string',
            ]);

            $reconciliation = $this->service->updateItems($id, $validated['items']);
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
