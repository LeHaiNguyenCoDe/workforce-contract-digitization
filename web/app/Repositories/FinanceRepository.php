<?php

namespace App\Repositories;

use App\Models\Fund;
use App\Models\FinanceTransaction;
use App\Repositories\Contracts\FinanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FinanceRepository implements FinanceRepositoryInterface
{
    /**
     * Get all active funds
     */
    public function getActiveFunds(): array
    {
        return Fund::active()->get()->toArray();
    }

    /**
     * Find fund by ID
     */
    public function findFundById(int $id): ?Fund
    {
        return Fund::find($id);
    }

    /**
     * Get default fund
     */
    public function getDefaultFund(): ?Fund
    {
        return Fund::where('is_default', true)->first() ?? Fund::first();
    }

    /**
     * Get transactions with filters
     */
    public function getTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = FinanceTransaction::with(['fund', 'category', 'warehouse', 'creator'])
            ->orderBy('transaction_date', 'desc');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['fund_id'])) {
            $query->where('fund_id', $filters['fund_id']);
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

        return $query->paginate($perPage);
    }

    /**
     * Find transaction by ID
     */
    public function findTransactionById(int $id): ?FinanceTransaction
    {
        return FinanceTransaction::with(['fund', 'category', 'warehouse', 'creator'])->find($id);
    }

    /**
     * Create transaction
     */
    public function createTransaction(array $data): FinanceTransaction
    {
        return FinanceTransaction::create($data);
    }

    /**
     * Update transaction
     */
    public function updateTransaction(FinanceTransaction $transaction, array $data): FinanceTransaction
    {
        $transaction->update($data);
        return $transaction->fresh(['fund', 'category', 'warehouse']);
    }

    /**
     * Delete transaction (soft)
     */
    public function deleteTransaction(int $id): bool
    {
        $transaction = FinanceTransaction::find($id);
        return $transaction ? $transaction->delete() : false;
    }

    /**
     * Get transaction summary by type
     */
    public function getTransactionSummary(string $fromDate, string $toDate): array
    {
        $transactions = FinanceTransaction::whereBetween('transaction_date', [$fromDate, $toDate])
            ->approved()
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        return [
            'total_receipt' => (float) ($transactions->where('type', 'receipt')->first()?->total ?? 0),
            'total_payment' => (float) ($transactions->where('type', 'payment')->first()?->total ?? 0),
        ];
    }
}
