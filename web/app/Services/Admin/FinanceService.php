<?php

namespace App\Services\Admin;

use App\Models\Fund;
use App\Models\FinanceTransaction;
use App\Models\Order;
use App\Models\DebtPayment;
use App\Models\AccountReceivable;
use App\Repositories\Contracts\FinanceRepositoryInterface;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;
use Exception;

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
    public function __construct(
        private FinanceRepositoryInterface $financeRepository
    ) {
    }

    /**
     * Record a receipt (thu tiền) - BR-FIN-01
     */
    public function recordReceipt(array $data): FinanceTransaction
    {
        return DB::transaction(function () use ($data) {
            $fund = Fund::findOrFail($data['fund_id'] ?? $this->getDefaultFundId());

            $balanceBefore = $fund->balance;
            $amount = (float) $data['amount'];

            // Update fund balance
            $fund->updateBalance($amount, 'receipt');

            // Create transaction
            $transaction = FinanceTransaction::create([
                'transaction_code' => FinanceTransaction::generateCode('receipt'),
                'fund_id' => $fund->id,
                'type' => FinanceTransaction::TYPE_RECEIPT,
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

            Helper::addLog([
                'action' => 'finance.receipt',
                'obj_action' => json_encode([$transaction->id, $amount]),
            ]);

            return $transaction;
        });
    }

    /**
     * Record a payment (chi tiền) - BR-FIN-02
     */
    public function recordPayment(array $data): FinanceTransaction
    {
        return DB::transaction(function () use ($data) {
            $fund = Fund::findOrFail($data['fund_id'] ?? $this->getDefaultFundId());
            $amount = (float) $data['amount'];

            // BR-FIN-03: Check balance
            if (!$fund->canWithdraw($amount)) {
                throw new Exception('Số dư quỹ không đủ để chi!');
            }

            $balanceBefore = $fund->balance;

            // Update fund balance
            $fund->updateBalance($amount, 'payment');

            // Create transaction
            $transaction = FinanceTransaction::create([
                'transaction_code' => FinanceTransaction::generateCode('payment'),
                'fund_id' => $fund->id,
                'type' => FinanceTransaction::TYPE_PAYMENT,
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

            Helper::addLog([
                'action' => 'finance.payment',
                'obj_action' => json_encode([$transaction->id, $amount]),
            ]);

            return $transaction;
        });
    }

    /**
     * Collect payment from order (BR-SALES-04 + BR-FIN-01)
     */
    public function collectOrderPayment(Order $order, float $amount, array $options = []): FinanceTransaction
    {
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
    }

    /**
     * Get all funds
     */
    public function getFunds(): array
    {
        return Fund::active()->get()->toArray();
    }

    /**
     * Get transactions with filters
     */
    public function getTransactions(array $filters = [], int $perPage = 15)
    {
        $query = FinanceTransaction::with(['fund', 'category', 'creator'])
            ->orderBy('transaction_date', 'desc');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['fund_id'])) {
            $query->where('fund_id', $filters['fund_id']);
        }

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->whereBetween('transaction_date', [$filters['from_date'], $filters['to_date']]);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get summary
     */
    public function getSummary(string $fromDate, string $toDate): array
    {
        $transactions = FinanceTransaction::whereBetween('transaction_date', [$fromDate, $toDate])
            ->where('status', FinanceTransaction::STATUS_APPROVED)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->get();

        $totalReceipt = $transactions->where('type', 'receipt')->first()?->total ?? 0;
        $totalPayment = $transactions->where('type', 'payment')->first()?->total ?? 0;

        $fundBalances = Fund::active()->get(['id', 'name', 'code', 'balance']);

        return [
            'total_receipt' => (float) $totalReceipt,
            'total_payment' => (float) $totalPayment,
            'net' => (float) $totalReceipt - (float) $totalPayment,
            'fund_balances' => $fundBalances,
        ];
    }

    // ===== EXPENSE METHODS (Consolidated from ExpenseService) =====

    /**
     * Get expenses list (replaces ExpenseService::getAll)
     */
    public function getExpenses(array $filters = [], int $perPage = 15)
    {
        $query = FinanceTransaction::with(['fund', 'category', 'warehouse', 'creator'])
            ->orderBy('transaction_date', 'desc');

        if (!empty($filters['type'])) {
            $type = $filters['type'] === 'expense' ? 'payment' :
                ($filters['type'] === 'income' ? 'receipt' : $filters['type']);
            $query->where('type', $type);
        }

        if (!empty($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['fund_id'])) {
            $query->where('fund_id', $filters['fund_id']);
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
     * Create expense/income (replaces ExpenseService::create)
     */
    public function createExpense(array $data): FinanceTransaction
    {
        $type = ($data['type'] ?? 'expense') === 'income' ? 'receipt' : 'payment';

        $methodName = $type === 'receipt' ? 'recordReceipt' : 'recordPayment';

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

        $transaction = $this->$methodName($transactionData);

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
     * Update expense (replaces ExpenseService::update)
     */
    public function updateExpense(int $id, array $data): FinanceTransaction
    {
        $transaction = FinanceTransaction::findOrFail($id);

        $updateData = [];
        if (isset($data['category_id']))
            $updateData['category_id'] = $data['category_id'];
        if (isset($data['warehouse_id']))
            $updateData['warehouse_id'] = $data['warehouse_id'];
        if (isset($data['description']))
            $updateData['description'] = $data['description'];
        if (isset($data['payment_method']))
            $updateData['payment_method'] = $data['payment_method'];
        if (isset($data['reference_number']))
            $updateData['reference_number'] = $data['reference_number'];
        if (isset($data['attachment']))
            $updateData['attachment'] = $data['attachment'];
        if (isset($data['is_recurring']))
            $updateData['is_recurring'] = $data['is_recurring'];
        if (isset($data['recurring_period']))
            $updateData['recurring_period'] = $data['recurring_period'];
        if (isset($data['status']))
            $updateData['status'] = $data['status'];

        $transaction->update($updateData);

        Helper::addLog([
            'action' => 'expense.update',
            'obj_action' => json_encode([$transaction->id]),
        ]);

        return $transaction->fresh(['category', 'warehouse', 'fund']);
    }

    /**
     * Delete expense (soft delete)
     */
    public function deleteExpense(int $id): bool
    {
        $transaction = FinanceTransaction::findOrFail($id);

        Helper::addLog([
            'action' => 'expense.delete',
            'obj_action' => json_encode([$transaction->id]),
        ]);

        return $transaction->delete();
    }

    /**
     * Get expense summary by category (replaces ExpenseService::getSummary)
     */
    public function getExpenseSummary(string $fromDate, string $toDate, ?int $warehouseId = null): array
    {
        $query = FinanceTransaction::selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->betweenDates($fromDate, $toDate)
            ->approved();

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $results = $query->groupBy('type')->get();

        $totalExpense = $results->where('type', 'payment')->first()?->total ?? 0;
        $totalIncome = $results->where('type', 'receipt')->first()?->total ?? 0;

        // Get by category
        $categoryQuery = FinanceTransaction::selectRaw('category_id, SUM(amount) as total')
            ->with('category:id,name,code')
            ->betweenDates($fromDate, $toDate)
            ->approved()
            ->whereNotNull('category_id');

        if ($warehouseId) {
            $categoryQuery->where('warehouse_id', $warehouseId);
        }

        $byCategory = $categoryQuery->groupBy('category_id')->get();

        return [
            'total_expense' => (float) $totalExpense,
            'total_income' => (float) $totalIncome,
            'net' => (float) $totalIncome - (float) $totalExpense,
            'by_category' => $byCategory,
        ];
    }

    private function getDefaultFundId(): int
    {
        $fund = Fund::where('is_default', true)->first();
        if (!$fund) {
            $fund = Fund::first();
        }
        if (!$fund) {
            throw new Exception('Chưa có quỹ tiền nào!');
        }
        return $fund->id;
    }
}
