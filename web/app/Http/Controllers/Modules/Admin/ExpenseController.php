<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Services\Admin\FinanceService;
use App\Services\Admin\ExpenseService;
use App\Http\Requests\Modules\Admin\ExpenseStoreRequest;
use App\Http\Requests\Modules\Admin\ExpenseUpdateRequest;
use App\Models\FinanceTransaction;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ExpenseController extends Controller
{
    use StoreApiResponse;

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

            return $this->successResponse([
                'items' => $expenses->items(),
                'meta' => [
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'total' => $expenses->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $expense = FinanceTransaction::with(['fund', 'category', 'warehouse', 'creator'])
                ->findOrFail($id);
            return $this->successResponse($expense);
        } catch (Exception $e) {
            return $this->notFoundResponse('not_found');
        }
    }

    public function store(ExpenseStoreRequest $request): JsonResponse
    {
        try {
            $expense = $this->financeService->createExpense($request->validated());
            return $this->createdResponse($expense, 'expense_created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function update(int $id, ExpenseUpdateRequest $request): JsonResponse
    {
        try {
            $expense = $this->financeService->updateExpense($id, $request->validated());
            return $this->updatedResponse($expense, 'expense_updated');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->financeService->deleteExpense($id);
            return $this->deletedResponse('expense_deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function categories(): JsonResponse
    {
        try {
            $categories = $this->expenseService->getCategories();
            return $this->successResponse($categories);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
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
            return $this->createdResponse($category, 'category_created_success');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
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
            return $this->successResponse($category);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function deleteCategory(int $id): JsonResponse
    {
        try {
            $this->expenseService->deleteCategory($id);
            return $this->deletedResponse('category_deleted_success');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function summary(Request $request): JsonResponse
    {
        try {
            $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
            $toDate = $request->input('to_date', now()->toDateString());
            $warehouseId = $request->input('warehouse_id');

            $summary = $this->financeService->getExpenseSummary($fromDate, $toDate, $warehouseId);
            return $this->successResponse($summary);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}



