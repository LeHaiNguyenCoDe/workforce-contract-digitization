<?php

namespace App\Services\Admin;

use App\Models\FinanceTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get P&L Report
     */
    public function getPnLReport(string $fromDate, string $toDate, ?int $warehouseId = null): array
    {
        // Revenue from orders
        $orderQuery = Order::query()
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->whereIn('status', ['completed', 'delivered']);

        if ($warehouseId) {
            $orderQuery->where('warehouse_id', $warehouseId);
        }

        $orderData = $orderQuery->selectRaw('
            SUM(total_amount) as revenue,
            0 as shipping_collected,
            COUNT(*) as order_count
        ')->first();

        $revenue = (float) ($orderData->revenue ?? 0);
        $shippingCollected = (float) ($orderData->shipping_collected ?? 0);
        $orderCount = (int) ($orderData->order_count ?? 0);

        // COGS from product_costs
        $cogs = DB::table('product_cost_history')
            ->where('action', 'outbound')
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->sum(DB::raw('ABS(quantity) * unit_cost'));

        // Expenses (từ finance_transactions type=payment)
        $expenseQuery = FinanceTransaction::query()
            ->where('type', 'payment')
            ->approved()
            ->betweenDates($fromDate, $toDate);

        if ($warehouseId) {
            $expenseQuery->where('warehouse_id', $warehouseId);
        }

        $totalExpenses = (float) $expenseQuery->sum('amount');

        // Expense breakdown by category
        $expensesByCategory = FinanceTransaction::query()
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category:id,name')
            ->where('type', 'payment')
            ->approved()
            ->betweenDates($fromDate, $toDate)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->get();

        // Other income (từ finance_transactions type=receipt không phải order)
        $otherIncome = FinanceTransaction::query()
            ->where('type', 'receipt')
            ->where(function ($q) {
                $q->whereNull('reference_type')
                    ->orWhere('reference_type', '!=', 'order');
            })
            ->approved()
            ->betweenDates($fromDate, $toDate)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->sum('amount');

        // Calculate
        $grossProfit = $revenue - (float) $cogs;
        $operatingIncome = $grossProfit - $totalExpenses + (float) $otherIncome;


        return [
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
            'revenue' => [
                'sales' => $revenue,
                'shipping_collected' => $shippingCollected,
                'other_income' => (float) $otherIncome,
                'total' => $revenue + (float) $otherIncome,
            ],
            'costs' => [
                'cogs' => (float) $cogs,
            ],
            'gross_profit' => $grossProfit,
            'gross_margin' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0,
            'expenses' => [
                'total' => $totalExpenses,
                'by_category' => $expensesByCategory,
            ],
            'operating_income' => $operatingIncome,
            'operating_margin' => $revenue > 0 ? round(($operatingIncome / $revenue) * 100, 2) : 0,
            'summary' => [
                'order_count' => $orderCount,
                'avg_order_value' => $orderCount > 0 ? round($revenue / $orderCount, 0) : 0,
            ],
        ];
    }

    /**
     * Get Sales Report
     */
    public function getSalesReport(string $fromDate, string $toDate, ?int $warehouseId = null): array
    {
        $query = Order::query()
            ->whereBetween('created_at', [$fromDate, $toDate . ' 23:59:59']);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        // By status
        $byStatus = (clone $query)
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('status')
            ->get();

        // By day
        $byDay = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Top products
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.created_at', [$fromDate, $toDate . ' 23:59:59'])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.qty) as total_qty'),
                DB::raw('SUM(order_items.qty * order_items.price) as total_value')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get();

        return [
            'by_status' => $byStatus,
            'by_day' => $byDay,
            'top_products' => $topProducts,
        ];
    }

    /**
     * Get Inventory Report
     */
    public function getInventoryReport(?int $warehouseId = null): array
    {
        $query = DB::table('stocks')
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->join('warehouses', 'warehouses.id', '=', 'stocks.warehouse_id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku',
                'warehouses.name as warehouse_name',
                'stocks.quantity'
            );

        if ($warehouseId) {
            $query->where('stocks.warehouse_id', $warehouseId);
        }

        $stocks = $query->orderBy('products.name')->get();

        // Low stock (need inventory_settings)
        $lowStock = DB::table('stocks')
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->join('inventory_settings', function ($join) {
                $join->on('inventory_settings.product_id', '=', 'stocks.product_id')
                    ->on('inventory_settings.warehouse_id', '=', 'stocks.warehouse_id');
            })
            ->whereColumn('stocks.quantity', '<', 'inventory_settings.min_quantity')
            ->select('products.id', 'products.name', 'stocks.quantity', 'inventory_settings.min_quantity')
            ->get();

        // Expiring soon
        $expiringSoon = DB::table('batches')
            ->join('products', 'products.id', '=', 'batches.product_id')
            ->where('batches.expiry_date', '<=', now()->addDays(30))
            ->where('batches.expiry_date', '>', now())
            ->where('batches.remaining_quantity', '>', 0)
            ->select('products.name', 'batches.batch_code', 'batches.expiry_date', 'batches.remaining_quantity')
            ->orderBy('batches.expiry_date')
            ->limit(20)
            ->get();

        return [
            'stocks' => $stocks,
            'low_stock_count' => $lowStock->count(),
            'low_stock' => $lowStock,
            'expiring_soon' => $expiringSoon,
        ];
    }

    /**
     * Get Dashboard Analytics - consolidated stats
     */
    public function getDashboardAnalytics(string $fromDate, string $toDate, ?int $warehouseId = null): array
    {
        // 1. Reports from existing methods
        $pnlReport = $this->getPnLReport($fromDate, $toDate, $warehouseId);
        $salesReport = $this->getSalesReport($fromDate, $toDate, $warehouseId);

        // 2. Customer stats
        $customerStats = DB::table('users')
            ->selectRaw('
                COUNT(*) as total,
                COUNT(CASE WHEN created_at >= ? THEN 1 END) as new_this_period
            ', [$fromDate])
            ->first();

        // 3. Low stock alert count
        $lowStockCount = DB::table('stocks')
            ->join('inventory_settings', function ($join) {
                $join->on('inventory_settings.product_id', '=', 'stocks.product_id')
                    ->on('inventory_settings.warehouse_id', '=', 'stocks.warehouse_id');
            })
            ->whereColumn('stocks.quantity', '<', 'inventory_settings.min_quantity')
            ->when($warehouseId, fn($q) => $q->where('stocks.warehouse_id', $warehouseId))
            ->count();

        // 4. Recent orders
        $recentOrders = Order::with(['user:id,name,email'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->latest()
            ->take(10)
            ->get(['id', 'code', 'user_id', 'total_amount', 'status', 'created_at'])
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->code,
                    'user_id' => $order->user_id,
                    'user' => $order->user,
                    'total' => (float) $order->total_amount,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ];
            });

        // 5. Pending actions
        $pendingOrders = Order::where('status', 'pending')->count();
        $pendingReturns = DB::table('returns')->where('status', 'requested')->count();
        $pendingPurchaseRequests = DB::table('purchase_requests')->where('status', 'pending')->count();

        return [
            'kpis' => [
                'revenue' => $pnlReport['revenue']['sales'] ?? 0,
                'gross_profit' => $pnlReport['gross_profit'] ?? 0,
                'gross_margin' => $pnlReport['gross_margin'] ?? 0,
                'order_count' => $pnlReport['summary']['order_count'] ?? 0,
                'avg_order_value' => $pnlReport['summary']['avg_order_value'] ?? 0,
                'customer_count' => $customerStats->total ?? 0,
                'new_customers' => $customerStats->new_this_period ?? 0,
            ],
            'revenue_chart' => $salesReport['by_day'] ?? [],
            'orders_by_status' => $salesReport['by_status'] ?? [],
            'top_products' => $salesReport['top_products'] ?? [],
            'recent_orders' => $recentOrders,
            'alerts' => [
                'low_stock' => $lowStockCount,
                'pending_orders' => $pendingOrders,
                'pending_returns' => $pendingReturns,
                'pending_purchase_requests' => $pendingPurchaseRequests,
            ],
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
            ],
        ];
    }
}
