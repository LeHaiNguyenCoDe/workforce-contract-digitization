<?php

namespace App\Repositories;

use App\Models\AccountReceivable;
use App\Models\AccountPayable;
use App\Repositories\Contracts\DebtRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DebtRepository implements DebtRepositoryInterface
{
    /**
     * Get receivables with filters
     */
    public function getReceivables(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AccountReceivable::with(['order', 'customer'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find receivable by ID
     */
    public function findReceivableById(int $id): ?AccountReceivable
    {
        return AccountReceivable::with(['order', 'customer', 'payments'])->find($id);
    }

    /**
     * Create receivable
     */
    public function createReceivable(array $data): AccountReceivable
    {
        return AccountReceivable::create($data);
    }

    /**
     * Update receivable
     */
    public function updateReceivable(AccountReceivable $receivable, array $data): AccountReceivable
    {
        $receivable->update($data);
        return $receivable->fresh();
    }

    /**
     * Get AR summary
     */
    public function getARSummary(): array
    {
        $receivables = AccountReceivable::query()
            ->selectRaw('status, COUNT(*) as count, SUM(remaining_amount) as total')
            ->groupBy('status')
            ->get();

        $totalOpen = $receivables->whereIn('status', ['open', 'partial', 'overdue'])
            ->sum('total');

        return [
            'total_receivable' => (float) $totalOpen,
            'by_status' => $receivables,
        ];
    }

    /**
     * Get payables with filters
     */
    public function getPayables(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = AccountPayable::with(['supplier'])
            ->orderBy('created_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find payable by ID
     */
    public function findPayableById(int $id): ?AccountPayable
    {
        return AccountPayable::with(['supplier', 'payments'])->find($id);
    }

    /**
     * Create payable
     */
    public function createPayable(array $data): AccountPayable
    {
        return AccountPayable::create($data);
    }

    /**
     * Update payable
     */
    public function updatePayable(AccountPayable $payable, array $data): AccountPayable
    {
        $payable->update($data);
        return $payable->fresh();
    }

    /**
     * Get AP summary
     */
    public function getAPSummary(): array
    {
        $payables = AccountPayable::query()
            ->selectRaw('status, COUNT(*) as count, SUM(remaining_amount) as total')
            ->groupBy('status')
            ->get();

        $totalOpen = $payables->whereIn('status', ['open', 'partial', 'overdue'])
            ->sum('total');

        return [
            'total_payable' => (float) $totalOpen,
            'by_status' => $payables,
        ];
    }
}
