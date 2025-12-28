<?php

namespace App\Http\Controllers;

use App\Services\FinanceService;
use App\Services\ExpenseService;
use App\Http\Requests\ExpenseStoreRequest;
use App\Http\Requests\ExpenseUpdateRequest;
use App\Models\FinanceTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

/**
 * ExpenseController - Thu chi quản lý
 * Sử dụng FinanceService cho transaction CRUD (consolidated)
 * Sử dụng ExpenseService chỉ cho category management
 */
class ExpenseController extends Controller
{
    public function __construct(
        private FinanceService $financeService,
        private ExpenseService $expenseService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['type', 'category_id', 'warehouse_id', 'fund_id', 'from_date', 'to_date', 'status']);
            $expenses = $this->financeService->getExpenses($filters, $request->input('per_page', 15));

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
            $expense = FinanceTransaction::with(['fund', 'category', 'warehouse', 'creator'])
                ->findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $expense]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 404);
        }
    }

    public function store(ExpenseStoreRequest $request): JsonResponse
    {
        try {
            $expense = $this->financeService->createExpense($request->validated());
            return response()->json(['status' => 'success', 'message' => 'Tạo thành công', 'data' => $expense], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function update(int $id, ExpenseUpdateRequest $request): JsonResponse
    {
        try {
            $expense = $this->financeService->updateExpense($id, $request->validated());
            return response()->json(['status' => 'success', 'message' => 'Cập nhật thành công', 'data' => $expense]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->financeService->deleteExpense($id);
            return response()->json(['status' => 'success', 'message' => 'Đã xóa']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    // ===== Category Management (still uses ExpenseService) =====

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

    public function updateCategory(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:100',
                'description' => 'nullable|string',
            ]);

            $category = $this->expenseService->updateCategory($id, $validated);
            return response()->json(['status' => 'success', 'data' => $category]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    public function deleteCategory(int $id): JsonResponse
    {
        try {
            $this->expenseService->deleteCategory($id);
            return response()->json(['status' => 'success', 'message' => 'Đã xóa danh mục']);
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

            $summary = $this->financeService->getExpenseSummary($fromDate, $toDate, $warehouseId);
            return response()->json(['status' => 'success', 'data' => $summary]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
