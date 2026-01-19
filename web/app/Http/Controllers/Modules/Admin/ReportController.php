<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Services\Admin\ReportService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ReportController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private ReportService $reportService
    ) {
    }

    public function pnl(Request $request): JsonResponse
    {
        try {
            $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
            $toDate = $request->input('to_date', now()->toDateString());
            $warehouseId = $request->input('warehouse_id');

            $report = $this->reportService->getPnLReport($fromDate, $toDate, $warehouseId);
            return $this->successResponse($report);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function sales(Request $request): JsonResponse
    {
        try {
            $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
            $toDate = $request->input('to_date', now()->toDateString());
            $warehouseId = $request->input('warehouse_id');

            $report = $this->reportService->getSalesReport($fromDate, $toDate, $warehouseId);
            return $this->successResponse($report);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function inventory(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');

            $report = $this->reportService->getInventoryReport($warehouseId);
            return $this->successResponse($report);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Dashboard Analytics - comprehensive stats for dashboard
     */
    public function dashboardAnalytics(Request $request): JsonResponse
    {
        try {
            // Get date range (default: last 30 days)
            $toDate = $request->input('to_date', now()->toDateString());
            $fromDate = $request->input('from_date', now()->subDays(30)->toDateString());
            $warehouseId = $request->input('warehouse_id');

            // 1. KPIs - using ReportService (with safe defaults)
            $pnlReport = null;
            $salesReport = null;

            try {
                $pnlReport = $this->reportService->getPnLReport($fromDate, $toDate, $warehouseId);
                $salesReport = $this->reportService->getSalesReport($fromDate, $toDate, $warehouseId);
            } catch (\Exception $e) {
                // Use empty defaults if reports fail
                $pnlReport = [
                    'revenue' => ['sales' => 0],
                    'gross_profit' => 0,
                    'gross_margin' => 0,
                    'summary' => ['order_count' => 0, 'avg_order_value' => 0],
                ];
                $salesReport = [
                    'by_day' => [],
                    'by_status' => [],
                    'top_products' => [],
                ];
            }

            // 2. Customer stats (safe query)
            $customerStats = null;
            try {
                $customerStats = \DB::table('users')
                    ->selectRaw('
                        COUNT(*) as total,
                        COUNT(CASE WHEN created_at >= ? THEN 1 END) as new_this_period
                    ', [$fromDate])
                    ->first();
            } catch (\Exception $e) {
                $customerStats = (object) ['total' => 0, 'new_this_period' => 0];
            }

            // 3. Revenue by period (for chart)
            $revenueByDay = $salesReport['by_day'] ?? [];

            // 4. Order status breakdown
            $ordersByStatus = $salesReport['by_status'] ?? [];

            // 5. Recent orders (last 10)
            $recentOrders = [];
            try {
                $recentOrders = \App\Models\Order::with(['user:id,name,email'])
                    ->latest()
                    ->take(10)
                    ->get(['id', 'order_number', 'user_id', 'total', 'status', 'created_at']);
            } catch (\Exception $e) {
                $recentOrders = [];
            }

            // 6. Low stock alert count (safe)
            $lowStockCount = 0;
            try {
                $lowStockCount = \DB::table('stocks')
                    ->join('inventory_settings', function ($join) {
                        $join->on('inventory_settings.product_id', '=', 'stocks.product_id')
                            ->on('inventory_settings.warehouse_id', '=', 'stocks.warehouse_id');
                    })
                    ->whereColumn('stocks.quantity', '<', 'inventory_settings.min_quantity')
                    ->count();
            } catch (\Exception $e) {
                $lowStockCount = 0;
            }

            // 7. Pending actions (safe)
            $pendingOrders = 0;
            $pendingReturns = 0;
            $pendingPurchaseRequests = 0;

            try {
                $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
            } catch (\Exception $e) {
            }

            try {
                $pendingReturns = \DB::table('return_slips')->where('status', 'pending')->count();
            } catch (\Exception $e) {
            }

            try {
                $pendingPurchaseRequests = \DB::table('purchase_requests')->where('status', 'pending')->count();
            } catch (\Exception $e) {
            }

            return $this->successResponse([
                'kpis' => [
                    'revenue' => $pnlReport['revenue']['sales'] ?? 0,
                    'gross_profit' => $pnlReport['gross_profit'] ?? 0,
                    'gross_margin' => $pnlReport['gross_margin'] ?? 0,
                    'order_count' => $pnlReport['summary']['order_count'] ?? 0,
                    'avg_order_value' => $pnlReport['summary']['avg_order_value'] ?? 0,
                    'customer_count' => $customerStats->total ?? 0,
                    'new_customers' => $customerStats->new_this_period ?? 0,
                ],
                'revenue_chart' => $revenueByDay,
                'orders_by_status' => $ordersByStatus,
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
            ]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}



