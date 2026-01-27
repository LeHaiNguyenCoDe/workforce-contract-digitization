<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;

use App\Services\Admin\FinanceService;
use App\Services\Admin\ExpenseService;
use App\Http\Requests\Modules\Admin\ExpenseStoreRequest;
use App\Http\Requests\Modules\Admin\ExpenseUpdateRequest;
use App\Http\Requests\Modules\Admin\ExpenseCategoryStoreRequest;
use App\Http\Requests\Modules\Admin\ExpenseCategoryUpdateRequest;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            $perPage = $request->input('per_page', 15);
            $expenses = $this->financeService->getExpenses($filters, $perPage);

            return $this->paginatedResponse($expenses);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $expense = $this->financeService->getExpenseById($id);
            return $this->successResponse($expense);
        } catch (NotFoundException $e) {
            return $this->notFoundResponse('not_found');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function store(ExpenseStoreRequest $request): JsonResponse
    {
        try {
            $expense = $this->financeService->createExpense($request->validated());
            return $this->createdResponse($expense, 'expense_created');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function update(int $id, ExpenseUpdateRequest $request): JsonResponse
    {
        try {
            $expense = $this->financeService->updateExpense($id, $request->validated());
            return $this->updatedResponse($expense, 'expense_updated');
        } catch (NotFoundException $e) {
            return $this->notFoundResponse('not_found');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->financeService->deleteExpense($id);
            return $this->deletedResponse('expense_deleted');
        } catch (NotFoundException $e) {
            return $this->notFoundResponse('not_found');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function categories(): JsonResponse
    {
        try {
            $categories = $this->expenseService->getCategories();
            return $this->successResponse($categories);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function createCategory(ExpenseCategoryStoreRequest $request): JsonResponse
    {
        try {
            $category = $this->expenseService->createCategory($request->validated());
            return $this->createdResponse($category, 'category_created_success');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function updateCategory(ExpenseCategoryUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $category = $this->expenseService->updateCategory($id, $request->validated());
            return $this->updatedResponse($category, 'category_updated');
        } catch (NotFoundException $e) {
            return $this->notFoundResponse('category_not_found');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function deleteCategory(int $id): JsonResponse
    {
        try {
            $this->expenseService->deleteCategory($id);
            return $this->deletedResponse('category_deleted_success');
        } catch (NotFoundException $e) {
            return $this->notFoundResponse('category_not_found');
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
