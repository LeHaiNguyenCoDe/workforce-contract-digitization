<?php

namespace Database\Seeders;

use App\Models\AccountPayable;
use App\Models\AccountReceivable;
use App\Models\DebtPayment;
use App\Models\ExpenseCategory;
use App\Models\FinanceTransaction;
use App\Models\Fund;
use App\Models\InboundBatch;
use App\Models\Order;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $funds = Fund::all();
        $warehouses = Warehouse::all();
        $categories = ExpenseCategory::all();

        if ($funds->isEmpty() || $warehouses->isEmpty()) {
            return;
        }

        // Set initial balance for funds
        $cashFund = Fund::where('code', 'CASH')->first();
        $bankFund = Fund::where('code', 'VCB')->first();

        if ($cashFund) {
            $cashFund->update(['balance' => 50000000, 'initial_balance' => 50000000]);
        }
        if ($bankFund) {
            $bankFund->update(['balance' => 200000000, 'initial_balance' => 200000000]);
        }

        // 1. Seed Account Payables from Inbound Batches
        $batches = InboundBatch::where('status', InboundBatch::STATUS_COMPLETED)->get();
        foreach ($batches as $batch) {
            $totalAmount = rand(5000000, 20000000);
            AccountPayable::create([
                'ap_code' => AccountPayable::generateCode(),
                'supplier_id' => $batch->supplier_id,
                'reference_type' => 'inbound_batch',
                'reference_id' => $batch->id,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'remaining_amount' => $totalAmount,
                'due_date' => Carbon::now()->addDays(rand(7, 30)),
                'status' => AccountPayable::STATUS_OPEN,
                'notes' => 'Công nợ từ lô nhập ' . $batch->batch_number,
            ]);
        }

        // 2. Seed Account Receivables from Orders
        $orders = Order::take(5)->get();
        foreach ($orders as $order) {
            AccountReceivable::create([
                'ar_code' => AccountReceivable::generateCode(),
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'total_amount' => $order->total_amount,
                'paid_amount' => 0,
                'remaining_amount' => $order->total_amount,
                'due_date' => Carbon::now()->addDays(rand(3, 15)),
                'status' => AccountReceivable::STATUS_OPEN,
                'notes' => 'Công nợ từ đơn hàng ' . $order->order_number,
            ]);
        }

        // 3. Seed comprehensive Finance Transactions
        $this->seedMonthlyTransactions($cashFund, $bankFund, $admin, $warehouses->first(), $categories);
        $this->seedDebtPayments($funds, $admin);

        $this->command->info('Finance data seeded successfully!');
    }

    private function seedMonthlyTransactions($cashFund, $bankFund, $admin, $warehouse, $categories)
    {
        // Chi phí cố định hàng tháng - 3 tháng gần nhất
        for ($month = 2; $month >= 0; $month--) {
            $monthDate = Carbon::now()->subMonths($month);

            // Chi phí thuê mặt bằng
            $this->createTransaction($bankFund, $admin, $warehouse, $categories, [
                'type' => 'payment',
                'amount' => 15000000,
                'date' => $monthDate->copy()->startOfMonth()->addDays(4),
                'category_code' => 'RENT',
                'description' => 'Thuê mặt bằng xưởng gốm tháng ' . $monthDate->format('m/Y'),
            ]);

            // Chi phí điện (cao do lò nung)
            $this->createTransaction($bankFund, $admin, $warehouse, $categories, [
                'type' => 'payment',
                'amount' => rand(10000000, 15000000),
                'date' => $monthDate->copy()->startOfMonth()->addDays(10),
                'category_code' => 'ELECTRIC',
                'description' => 'Tiền điện lò nung tháng ' . $monthDate->format('m/Y'),
            ]);

            // Chi phí nước
            $this->createTransaction($cashFund, $admin, $warehouse, $categories, [
                'type' => 'payment',
                'amount' => rand(1200000, 2000000),
                'date' => $monthDate->copy()->startOfMonth()->addDays(12),
                'category_code' => 'WATER',
                'description' => 'Tiền nước ngâm đất sét tháng ' . $monthDate->format('m/Y'),
            ]);

            // Lương nghệ nhân
            $this->createTransaction($bankFund, $admin, $warehouse, $categories, [
                'type' => 'payment',
                'amount' => 45000000,
                'date' => $monthDate->copy()->startOfMonth()->addDays(5),
                'category_code' => 'SALARY',
                'description' => 'Lương nghệ nhân và thợ gốm tháng ' . $monthDate->format('m/Y'),
            ]);

            // Thu từ bán hàng (nhiều giao dịch trong tháng)
            for ($i = 0; $i < rand(8, 15); $i++) {
                $this->createTransaction($bankFund, $admin, $warehouse, $categories, [
                    'type' => 'receipt',
                    'amount' => rand(2000000, 25000000),
                    'date' => $monthDate->copy()->startOfMonth()->addDays(rand(1, 28)),
                    'category_code' => 'SALES',
                    'description' => 'Thu tiền bán hàng - Đơn hàng gốm sứ',
                ]);
            }

            // Thu tiền mặt tại cửa hàng
            for ($i = 0; $i < rand(5, 10); $i++) {
                $this->createTransaction($cashFund, $admin, $warehouse, $categories, [
                    'type' => 'receipt',
                    'amount' => rand(500000, 5000000),
                    'date' => $monthDate->copy()->startOfMonth()->addDays(rand(1, 28)),
                    'category_code' => 'SALES',
                    'description' => 'Bán hàng tại cửa hàng - Tiền mặt',
                ]);
            }
        }

        // Chi phí nguyên vật liệu gốm sứ
        $materials = [
            ['name' => 'Nhập đất sét cao lanh', 'amount' => 8000000],
            ['name' => 'Nhập men gốm nhập khẩu', 'amount' => 12000000],
            ['name' => 'Mua than / củi nung lò', 'amount' => 5000000],
            ['name' => 'Mua vật liệu đóng gói', 'amount' => 3000000],
        ];

        foreach ($materials as $material) {
            $this->createTransaction($bankFund, $admin, $warehouse, $categories, [
                'type' => 'payment',
                'amount' => $material['amount'],
                'date' => Carbon::now()->subDays(rand(5, 20)),
                'category_code' => 'OTHER_EXP',
                'description' => $material['name'],
            ]);
        }

        // Chi phí quảng cáo và marketing
        $this->createTransaction($bankFund, $admin, $warehouse, $categories, [
            'type' => 'payment',
            'amount' => 5500000,
            'date' => Carbon::now()->subDays(7),
            'category_code' => 'ADS',
            'description' => 'Chạy quảng cáo Facebook + TikTok Shop',
        ]);

        // Chi phí vận chuyển
        $this->createTransaction($cashFund, $admin, $warehouse, $categories, [
            'type' => 'payment',
            'amount' => 2500000,
            'date' => Carbon::now()->subDays(3),
            'category_code' => 'SHIPPING',
            'description' => 'Phí giao hàng tuần 51/2024',
        ]);

        // Thu COD từ đơn vị vận chuyển
        $this->createTransaction($bankFund, $admin, $warehouse, $categories, [
            'type' => 'receipt',
            'amount' => 35000000,
            'date' => Carbon::now()->subDays(2),
            'category_code' => 'COD_INCOME',
            'description' => 'Thu tiền COD từ GHTK tuần 51/2024',
        ]);
    }

    private function createTransaction($fund, $admin, $warehouse, $categories, $data)
    {
        $category = $categories->where('code', $data['category_code'])->first();
        $balanceBefore = $fund->balance;

        if ($data['type'] === 'receipt') {
            $balanceAfter = $balanceBefore + $data['amount'];
        } else {
            $balanceAfter = $balanceBefore - $data['amount'];
        }

        FinanceTransaction::create([
            'transaction_code' => FinanceTransaction::generateCode($data['type']),
            'fund_id' => $fund->id,
            'type' => $data['type'],
            'amount' => $data['amount'],
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'transaction_date' => $data['date'],
            'category_id' => $category?->id,
            'warehouse_id' => $warehouse->id,
            'description' => $data['description'],
            'payment_method' => $fund->type === 'bank' ? 'bank_transfer' : 'cash',
            'created_by' => $admin->id,
            'status' => FinanceTransaction::STATUS_APPROVED,
        ]);

        // Update fund balance
        $fund->update(['balance' => $balanceAfter]);
    }

    private function seedDebtPayments($funds, $admin)
    {
        // Trả bớt một ít công nợ AP
        $aps = AccountPayable::take(2)->get();
        foreach ($aps as $ap) {
            $paymentAmount = $ap->total_amount * 0.5;
            $fund = $funds->random();

            $transaction = FinanceTransaction::create([
                'transaction_code' => FinanceTransaction::generateCode('payment'),
                'fund_id' => $fund->id,
                'type' => FinanceTransaction::TYPE_PAYMENT,
                'amount' => $paymentAmount,
                'balance_before' => $fund->balance,
                'balance_after' => $fund->balance - $paymentAmount,
                'transaction_date' => Carbon::now(),
                'description' => 'Thanh toán một phần công nợ ' . $ap->ap_code,
                'payment_method' => 'bank_transfer',
                'created_by' => $admin->id,
                'status' => FinanceTransaction::STATUS_APPROVED,
            ]);

            DebtPayment::create([
                'debt_id' => $ap->id,
                'debt_type' => 'ap',
                'finance_transaction_id' => $transaction->id,
                'amount' => $paymentAmount,
                'payment_date' => Carbon::now(),
                'notes' => 'Thanh toán mẫu',
            ]);

            $ap->recordPayment($paymentAmount);
            $fund->update(['balance' => $fund->balance - $paymentAmount]);
        }

        // Thu tiền công nợ AR
        $ars = AccountReceivable::take(2)->get();
        foreach ($ars as $ar) {
            $receiptAmount = $ar->total_amount * 0.7;
            $fund = $funds->random();

            $transaction = FinanceTransaction::create([
                'transaction_code' => FinanceTransaction::generateCode('receipt'),
                'fund_id' => $fund->id,
                'type' => FinanceTransaction::TYPE_RECEIPT,
                'amount' => $receiptAmount,
                'balance_before' => $fund->balance,
                'balance_after' => $fund->balance + $receiptAmount,
                'transaction_date' => Carbon::now(),
                'description' => 'Thu tiền công nợ ' . $ar->ar_code,
                'payment_method' => 'bank_transfer',
                'created_by' => $admin->id,
                'status' => FinanceTransaction::STATUS_APPROVED,
            ]);

            DebtPayment::create([
                'debt_id' => $ar->id,
                'debt_type' => 'ar',
                'finance_transaction_id' => $transaction->id,
                'amount' => $receiptAmount,
                'payment_date' => Carbon::now(),
                'notes' => 'Thu tiền công nợ mẫu',
            ]);

            $ar->recordPayment($receiptAmount);
            $fund->update(['balance' => $fund->balance + $receiptAmount]);
        }
    }
}
