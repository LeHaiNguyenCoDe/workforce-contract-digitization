<?php

namespace App\Repositories\Contracts;

use App\Models\AccountReceivable;
use App\Models\AccountPayable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DebtRepositoryInterface
{
    /**
     * Get receivables with filters
     */
    public function getReceivables(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find receivable by ID
     */
    public function findReceivableById(int $id): ?AccountReceivable;

    /**
     * Create receivable
     */
    public function createReceivable(array $data): AccountReceivable;

    /**
     * Update receivable
     */
    public function updateReceivable(AccountReceivable $receivable, array $data): AccountReceivable;

    /**
     * Get AR summary
     */
    public function getARSummary(): array;

    /**
     * Get payables with filters
     */
    public function getPayables(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find payable by ID
     */
    public function findPayableById(int $id): ?AccountPayable;

    /**
     * Create payable
     */
    public function createPayable(array $data): AccountPayable;

    /**
     * Update payable
     */
    public function updatePayable(AccountPayable $payable, array $data): AccountPayable;

    /**
     * Get AP summary
     */
    public function getAPSummary(): array;
}
