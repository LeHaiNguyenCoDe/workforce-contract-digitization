<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use App\Models\FinanceTransaction;

/**
 * ExpenseService - Category Management Only
 * 
 * Note: Transaction CRUD đã chuyển sang FinanceService
 * Service này chỉ quản lý expense_categories
 */
class ExpenseService
{
    /**
     * Get all active categories
     */
    public function getCategories(): array
    {
        return ExpenseCategory::active()->orderBy('name')->get()->toArray();
    }

    /**
     * Get categories by type
     */
    public function getCategoriesByType(string $type): array
    {
        return ExpenseCategory::active()
            ->where('type', $type)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    /**
     * Create category
     */
    public function createCategory(array $data): ExpenseCategory
    {
        return ExpenseCategory::create($data);
    }

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): ExpenseCategory
    {
        $category = ExpenseCategory::findOrFail($id);
        $category->update($data);
        return $category;
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool
    {
        $category = ExpenseCategory::findOrFail($id);

        // Check if category has transactions (in finance_transactions)
        $hasTransactions = FinanceTransaction::where('category_id', $id)->exists();

        if ($hasTransactions) {
            throw new \Exception('Không thể xóa danh mục đang có giao dịch!');
        }

        return $category->delete();
    }
}
