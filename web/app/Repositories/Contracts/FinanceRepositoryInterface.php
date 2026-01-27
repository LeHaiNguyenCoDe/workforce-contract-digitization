<?php

namespace App\Repositories\Contracts;

use App\Models\Fund;
use App\Models\FinanceTransaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FinanceRepositoryInterface
{
    /**
     * Get all active funds
     */
    public function getActiveFunds(): array;

    /**
     * Find fund by ID
     */
    public function findFundById(int $id): ?Fund;

    /**
     * Get default fund
     */
    public function getDefaultFund(): ?Fund;

    /**
     * Get transactions with filters
     */
    public function getTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find transaction by ID
     */
    public function findTransactionById(int $id): ?FinanceTransaction;

    /**
     * Create transaction
     */
    public function createTransaction(array $data): FinanceTransaction;

    /**
     * Update transaction
     */
    public function updateTransaction(FinanceTransaction $transaction, array $data): FinanceTransaction;

    /**
     * Delete transaction (soft)
     */
    public function deleteTransaction(int $id): bool;

    /**
     * Get transaction summary by type
     */
    public function getTransactionSummary(string $fromDate, string $toDate): array;
}
