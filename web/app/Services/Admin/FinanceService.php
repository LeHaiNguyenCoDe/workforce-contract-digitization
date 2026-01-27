<?php

namespace App\Services\Admin;

use App\Exceptions\BusinessLogicException;
use App\Exceptions\NotFoundException;
use App\Models\Fund;
use App\Models\FinanceTransaction;
use App\Models\Order;
use App\Models\DebtPayment;
use App\Repositories\Contracts\FinanceRepositoryInterface;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Finance Service
 * Handles all finance transactions (receipts/payments)
 * 
 * BR-FIN-01: Thu tiền bán hàng
 * BR-FIN-02: Chi tiền
 * BR-FIN-03: Không cho âm quỹ
 */
class FinanceService
{
    /**
     * Allowed fields for expense update
     */
    private const EXPENSE_UPDATE_FIELDS = [
        'category_id',
        'warehouse_id',
        'description',
        'payment_method',
        'reference_number',
        'attachment',
        'is_recurring',
        'recurring_period',
        'status',
    ];

    /**
     * Cached default fund ID
     */
    private ?int $cachedDefaultFundId = null;

    public function __construct(
        private FinanceRepositoryInterface $financeRepository
    ) {
    }

    // ==================== CORE TRANSACTION METHODS ====================

    /**
     * Record a receipt (thu tiền) - BR-FIN-01
     */
    public function recordReceipt(array $data): FinanceTransaction
    {
        return $this->createTransaction($data, FinanceTransaction::TYPE_RECEIPT);
    }

    /**
     * Record a payment (chi tiền) - BR-FIN-02
     */
    public function recordPayment(array $data): FinanceTransaction
    {
        return $this->createTransaction($data, FinanceTransaction::TYPE_PAYMENT);
    }

    /**
     * Create a finance transaction (unified method for receipt/payment)
     * 
     * @throws BusinessLogicException
     */
    private function createTransaction(array $data, string $type): FinanceTransaction
    {
        return DB::transaction(function () use ($data, $type) {
            $fund = $this->resolveFund($data['fund_id'] ?? null);
            $amount = (float) $data['amount'];

            // BR-FIN-03: Check balance for payments
            if ($type === FinanceTransaction::TYPE_PAYMENT && !$fund->canWithdraw($amount)) {
                throw new BusinessLogicException('Số dư quỹ không đủ để chi!');
            }

            $balanceBefore = $fund->balance;
            $operationType = $type === FinanceTransaction::TYPE_RECEIPT ? 'receipt' : 'payment';
            
            // Update fund balance
            $fund->updateBalance($amount, $operationType);

            // Create transaction via repository
            $transaction = $this->financeRepository->createTransaction([
                'transaction_code' => FinanceTransaction::generateCode($operationType),
                'fund_id' => $fund->id,
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $fund->balance,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'description' => $data['description'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'created_by' => auth()->id(),
                'status' => FinanceTransaction::STATUS_APPROVED,
            ]);

            $this->logAction("finance.{$operationType}", [$transaction->id, $amount]);

            return $transaction;
        });
    }

    /**
     * Collect payment from order (BR-SALES-04 + BR-FIN-01)
     */
    public function collectOrderPayment(Order $order, float $amount, array $options = []): FinanceTransaction
    {
        return DB::transaction(function () use ($order, $amount, $options) {
            $transaction = $this->recordReceipt([
                'fund_id' => $options['fund_id'] ?? null,
                'amount' => $amount,
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'description' => "Thu tiền đơn hàng {$order->code}",
                'payment_method' => $options['payment_method'] ?? $order->payment_method,
            ]);

            // Update order paid amount
            $order->paid_amount = (float) $order->paid_amount + $amount;
            $order->remaining_amount = (float) $order->total_amount - (float) $order->paid_amount;
            $order->save();

            // Update AR if exists
            if ($order->receivable) {
                $order->receivable->recordPayment($amount);

                DebtPayment::create([
                    'debt_type' => DebtPayment::TYPE_AR,
                    'debt_id' => $order->receivable->id,
                    'finance_transaction_id' => $transaction->id,
                    'amount' => $amount,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => $options['payment_method'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }

            return $transaction;
        });
    }

    // ==================== FUND METHODS ====================

    /**
     * Get all active funds
     */
    public function getFunds(): array
    {
        return Fund::active()->get()->toArray();
    }

    /**
     * Resolve fund by ID or get default
     * 
     * @throws NotFoundException
     */
    private function resolveFund(?int $fundId): Fund
    {
        if ($fundId) {
            $fund = $this->financeRepository->findFundById($fundId);
            if (!$fund) {
                throw new NotFoundException("Quỹ với ID {$fundId} không tồn tại!");
            }
            return $fund;
        }

        return $this->getDefaultFund();
    }

    /**
     * Get default fund (cached)
     * 
     * @throws BusinessLogicException
     */
    private function getDefaultFund(): Fund
    {
        if ($this->cachedDefaultFundId) {
            $fund = $this->financeRepository->findFundById($this->cachedDefaultFundId);
            if ($fund) return $fund;
        }

        $fund = $this->financeRepository->getDefaultFund();
        
        if (!$fund) {
            throw new BusinessLogicException('Chưa có quỹ tiền nào!');
        }

        $this->cachedDefaultFundId = $fund->id;
        return $fund;
    }

    // ==================== TRANSACTION QUERY METHODS ====================

    /**
     * Get expense/transaction by ID
     * 
     * @throws NotFoundException
     */
    public function getExpenseById(int $id): FinanceTransaction
    {
        $expense = $this->financeRepository->findTransactionById($id);
        
        if (!$expense) {
            throw new NotFoundException("Transaction with ID {$id} not found");
        }
        
        return $expense;
    }

    /**
     * Get transactions with filters
     */
    public function getTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->financeRepository->getTransactions($filters, $perPage);
    }

    /**
     * Get expenses list with extended filters
     */
    public function getExpenses(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = FinanceTransaction::with(['fund', 'category', 'warehouse', 'creator'])
            ->orderBy('transaction_date', 'desc');

        // Map expense/income to payment/receipt
        if (!empty($filters['type'])) {
            $filters['type'] = match($filters['type']) {
                'expense' => 'payment',
                'income' => 'receipt',
                default => $filters['type'],
            };
        }

        $this->applyBasicFilters($query, $filters);
        $this->applyExtendedFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Apply basic filters to query
     */
    private function applyBasicFilters($query, array $filters): void
    {
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['fund_id'])) {
            $query->where('fund_id', $filters['fund_id']);
        }

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->whereBetween('transaction_date', [$filters['from_date'], $filters['to_date']]);
        }
    }

    /**
     * Apply extended filters (for expenses)
     */
    private function applyExtendedFilters($query, array $filters): void
    {
        if (!empty($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
    }

    // ==================== SUMMARY METHODS ====================

    /**
     * Get financial summary for date range
     */
    public function getSummary(string $fromDate, string $toDate): array
    {
        $totals = $this->getTransactionTotals($fromDate, $toDate);
        $fundBalances = Fund::active()->get(['id', 'name', 'code', 'balance']);

        return [
            'total_receipt' => $totals['receipt'],
            'total_payment' => $totals['payment'],
            'net' => $totals['receipt'] - $totals['payment'],
            'fund_balances' => $fundBalances,
        ];
    }

    /**
     * Get expense summary by category
     */
    public function getExpenseSummary(string $fromDate, string $toDate, ?int $warehouseId = null): array
    {
        $baseQuery = fn() => FinanceTransaction::betweenDates($fromDate, $toDate)
            ->approved()
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId));

        // Get totals by type
        $results = $baseQuery()
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $totalExpense = (float) ($results->where('type', 'payment')->first()?->total ?? 0);
        $totalIncome = (float) ($results->where('type', 'receipt')->first()?->total ?? 0);

        // Get by category
        $byCategory = $baseQuery()
            ->selectRaw('category_id, SUM(amount) as total')
            ->with('category:id,name,code')
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->get();

        return [
            'total_expense' => $totalExpense,
            'total_income' => $totalIncome,
            'net' => $totalIncome - $totalExpense,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get transaction totals for date range
     */
    private function getTransactionTotals(string $fromDate, string $toDate): array
    {
        $transactions = FinanceTransaction::whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', FinanceTransaction::STATUS_APPROVED)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        return [
            'receipt' => (float) ($transactions->where('type', 'receipt')->first()?->total ?? 0),
            'payment' => (float) ($transactions->where('type', 'payment')->first()?->total ?? 0),
        ];
    }

    // ==================== EXPENSE CRUD METHODS ====================

    /**
     * Create expense/income transaction
     */
    public function createExpense(array $data): FinanceTransaction
    {
        $type = ($data['type'] ?? 'expense') === 'income' ? 'receipt' : 'payment';
        $method = $type === 'receipt' ? 'recordReceipt' : 'recordPayment';

        $transactionData = [
            'fund_id' => $data['fund_id'] ?? null,
            'amount' => $data['amount'],
            'transaction_date' => $data['expense_date'] ?? $data['transaction_date'] ?? now()->toDateString(),
            'category_id' => $data['category_id'] ?? null,
            'reference_type' => 'expense',
            'reference_number' => $data['reference_number'] ?? null,
            'description' => $data['description'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
        ];

        $transaction = $this->$method($transactionData);

        // Update with expense-specific fields
        $transaction->update([
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'attachment' => $data['attachment'] ?? null,
            'is_recurring' => $data['is_recurring'] ?? false,
            'recurring_period' => $data['recurring_period'] ?? null,
        ]);

        return $transaction->fresh(['category', 'warehouse', 'fund']);
    }

    /**
     * Update expense transaction
     * 
     * @throws NotFoundException
     */
    public function updateExpense(int $id, array $data): FinanceTransaction
    {
        $transaction = FinanceTransaction::find($id);
        
        if (!$transaction) {
            throw new NotFoundException("Transaction with ID {$id} not found");
        }

        // Only update allowed fields
        $updateData = array_intersect_key($data, array_flip(self::EXPENSE_UPDATE_FIELDS));

        if (!empty($updateData)) {
            $transaction->update($updateData);
            $this->logAction('expense.update', [$transaction->id]);
        }

        return $transaction->fresh(['category', 'warehouse', 'fund']);
    }

    /**
     * Delete expense transaction (soft delete)
     * 
     * @throws NotFoundException
     */
    public function deleteExpense(int $id): bool
    {
        $transaction = $this->financeRepository->findTransactionById($id);
        
        if (!$transaction) {
            throw new NotFoundException("Transaction with ID {$id} not found");
        }

        $this->logAction('expense.delete', [$transaction->id]);

        return $this->financeRepository->deleteTransaction($id);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Log action to activity log
     */
    private function logAction(string $action, array $data): void
    {
        Helper::addLog([
            'action' => $action,
            'obj_action' => json_encode($data),
        ]);
    }
}
