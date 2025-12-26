<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Helpers\Helper;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    /**
     * Get all expenses with pagination
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Expense::query()
            ->with(['category:id,name,code', 'warehouse:id,name', 'creator:id,name']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->betweenDates($filters['from_date'], $filters['to_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('expense_date', 'desc')->paginate($perPage);
    }

    /**
     * Get expense by ID
     */
    public function getById(int $id): Expense
    {
        return Expense::with(['category', 'warehouse', 'creator', 'approver'])
            ->findOrFail($id);
    }

    /**
     * Create expense
     */
    public function create(array $data): Expense
    {
        $data['expense_code'] = Expense::generateCode($data['type'] ?? 'expense');
        $data['created_by'] = auth()->id();

        $expense = Expense::create($data);

        Helper::addLog([
            'action' => 'expense.create',
            'obj_action' => json_encode([$expense->id]),
        ]);

        return $expense->load(['category', 'warehouse']);
    }

    /**
     * Update expense
     */
    public function update(int $id, array $data): Expense
    {
        $expense = Expense::findOrFail($id);
        $expense->update($data);

        Helper::addLog([
            'action' => 'expense.update',
            'obj_action' => json_encode([$expense->id]),
        ]);

        return $expense->load(['category', 'warehouse']);
    }

    /**
     * Delete expense
     */
    public function delete(int $id): bool
    {
        $expense = Expense::findOrFail($id);

        Helper::addLog([
            'action' => 'expense.delete',
            'obj_action' => json_encode([$expense->id]),
        ]);

        return $expense->delete();
    }

    /**
     * Get categories
     */
    public function getCategories(): array
    {
        return ExpenseCategory::active()->orderBy('name')->get()->toArray();
    }

    /**
     * Create category
     */
    public function createCategory(array $data): ExpenseCategory
    {
        return ExpenseCategory::create($data);
    }

    /**
     * Get summary by period
     */
    public function getSummary(string $fromDate, string $toDate, ?int $warehouseId = null): array
    {
        $query = Expense::query()
            ->select([
                'type',
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count'),
            ])
            ->betweenDates($fromDate, $toDate)
            ->where('status', 'approved');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $results = $query->groupBy('type')->get();

        $totalExpense = $results->where('type', 'expense')->first()?->total ?? 0;
        $totalIncome = $results->where('type', 'income')->first()?->total ?? 0;

        // Get by category
        $byCategory = Expense::query()
            ->select([
                'category_id',
                DB::raw('SUM(amount) as total'),
            ])
            ->with('category:id,name')
            ->betweenDates($fromDate, $toDate)
            ->where('status', 'approved')
            ->groupBy('category_id')
            ->get();

        return [
            'total_expense' => (float)$totalExpense,
            'total_income' => (float)$totalIncome,
            'net' => (float)$totalIncome - (float)$totalExpense,
            'by_category' => $byCategory,
        ];
    }
}
