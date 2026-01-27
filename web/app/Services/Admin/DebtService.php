<?php

namespace App\Services\Admin;

use App\Models\AccountReceivable;
use App\Models\AccountPayable;
use App\Models\Order;
use App\Repositories\Contracts\DebtRepositoryInterface;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;

/**
 * Debt Service
 * Handles AR (Account Receivable) and AP (Account Payable)
 * 
 * BR-DEBT-01: Công nợ phải thu khi order completed chưa thanh toán đủ
 * BR-DEBT-02: Công nợ phải trả khi nhập hàng chưa thanh toán
 */
class DebtService
{
    public function __construct(
        private DebtRepositoryInterface $debtRepository
    ) {
    }

    /**
     * Create receivable for an order (BR-DEBT-01)
     * Called when order is completed but not fully paid
     */
    public function createReceivableFromOrder(Order $order): ?AccountReceivable
    {
        // Only create if not fully paid
        $remainingAmount = (float) $order->total_amount - (float) $order->paid_amount;

        if ($remainingAmount <= 0) {
            return null;
        }

        // Check if already exists
        if ($order->receivable) {
            // Update existing
            $order->receivable->update([
                'total_amount' => $order->total_amount,
                'paid_amount' => $order->paid_amount,
                'remaining_amount' => $remainingAmount,
            ]);
            return $order->receivable;
        }

        $ar = AccountReceivable::create([
            'ar_code' => AccountReceivable::generateCode(),
            'order_id' => $order->id,
            'customer_id' => $order->user_id,
            'total_amount' => $order->total_amount,
            'paid_amount' => $order->paid_amount,
            'remaining_amount' => $remainingAmount,
            'due_date' => now()->addDays(30), // Default 30 days
            'status' => $order->paid_amount > 0
                ? AccountReceivable::STATUS_PARTIAL
                : AccountReceivable::STATUS_OPEN,
        ]);

        Helper::addLog([
            'action' => 'ar.create',
            'obj_action' => json_encode([$ar->id, $order->code, $remainingAmount]),
        ]);

        return $ar;
    }

    /**
     * Create payable for a purchase (BR-DEBT-02)
     */
    public function createPayable(array $data): AccountPayable
    {
        $ap = AccountPayable::create([
            'ap_code' => AccountPayable::generateCode(),
            'supplier_id' => $data['supplier_id'] ?? null,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'total_amount' => $data['total_amount'],
            'paid_amount' => 0,
            'remaining_amount' => $data['total_amount'],
            'due_date' => $data['due_date'] ?? now()->addDays(30),
            'status' => AccountPayable::STATUS_OPEN,
            'notes' => $data['notes'] ?? null,
        ]);

        Helper::addLog([
            'action' => 'ap.create',
            'obj_action' => json_encode([$ap->id, $data['total_amount']]),
        ]);

        return $ap;
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

    /**
     * Get all receivables with filters
     */
    public function getReceivables(array $filters = [], int $perPage = 15)
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
     * Get all payables with filters
     */
    public function getPayables(array $filters = [], int $perPage = 15)
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
     * Check and update overdue status for all AR
     */
    public function updateOverdueReceivables(): int
    {
        $count = AccountReceivable::where('due_date', '<', today())
            ->whereNotIn('status', [AccountReceivable::STATUS_PAID, AccountReceivable::STATUS_OVERDUE])
            ->update(['status' => AccountReceivable::STATUS_OVERDUE]);

        return $count;
    }
}
