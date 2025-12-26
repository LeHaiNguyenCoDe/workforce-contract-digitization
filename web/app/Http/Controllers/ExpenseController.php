<?php

namespace App\Http\Controllers;

use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['type', 'category_id', 'warehouse_id', 'from_date', 'to_date', 'status']);
            $expenses = $this->expenseService->getAll($filters, $request->input('per_page', 15));

            return response()->json([
                'status' => 'success',
                'data' => $expenses->items(),
                'meta' => [
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'total' => $expenses->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $expense = $this->expenseService->getById($id);
            return response()->json(['status' => 'success', 'data' => $expense]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:expense_categories,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'type' => 'required|in:expense,income',
                'amount' => 'required|numeric|min:0',
                'expense_date' => 'required|date',
                'payment_method' => 'nullable|string|max:50',
                'reference_number' => 'nullable|string|max:100',
                'description' => 'nullable|string',
            ]);

            $expense = $this->expenseService->create($validated);
            return response()->json(['status' => 'success', 'message' => 'Tạo thành công', 'data' => $expense], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category_id' => 'nullable|exists:expense_categories,id',
                'warehouse_id' => 'nullable|exists:warehouses,id',
                'type' => 'nullable|in:expense,income',
                'amount' => 'nullable|numeric|min:0',
                'expense_date' => 'nullable|date',
                'payment_method' => 'nullable|string|max:50',
                'reference_number' => 'nullable|string|max:100',
                'description' => 'nullable|string',
            ]);

            $expense = $this->expenseService->update($id, $validated);
            return response()->json(['status' => 'success', 'message' => 'Cập nhật thành công', 'data' => $expense]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->expenseService->delete($id);
            return response()->json(['status' => 'success', 'message' => 'Đã xóa']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function categories(): JsonResponse
    {
        try {
            $categories = $this->expenseService->getCategories();
            return response()->json(['status' => 'success', 'data' => $categories]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function createCategory(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:50|unique:expense_categories,code',
                'type' => 'required|in:expense,income',
                'description' => 'nullable|string',
            ]);

            $category = $this->expenseService->createCategory($validated);
            return response()->json(['status' => 'success', 'data' => $category], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function summary(Request $request): JsonResponse
    {
        try {
            $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
            $toDate = $request->input('to_date', now()->toDateString());
            $warehouseId = $request->input('warehouse_id');

            $summary = $this->expenseService->getSummary($fromDate, $toDate, $warehouseId);
            return response()->json(['status' => 'success', 'data' => $summary]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
