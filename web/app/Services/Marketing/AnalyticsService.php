<?php

namespace App\Services\Marketing;

use App\Models\MarketingAnalytics;
use App\Models\Lead;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Models\Order;
use App\Models\CustomerSegment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Record a metric
     */
    public function recordMetric(
        string $metricType,
        $metricValue,
        ?int $segmentId = null,
        ?int $campaignId = null,
        ?int $userId = null,
        ?array $metadata = null
    ): MarketingAnalytics {
        return MarketingAnalytics::create([
            'metric_type' => $metricType,
            'metric_value' => $metricValue,
            'metric_date' => now()->toDateString(),
            'segment_id' => $segmentId,
            'campaign_id' => $campaignId,
            'user_id' => $userId,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Get dashboard summary
     */
    public function getDashboardSummary(array $filters = []): array
    {
        $dateFrom = isset($filters['date_from']) ? Carbon::parse($filters['date_from']) : now()->subMonths(1);
        $dateTo = isset($filters['date_to']) ? Carbon::parse($filters['date_to']) : now();

        $leadsCreated = Lead::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $leadsConverted = Lead::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', Lead::STATUS_CONVERTED)
            ->count();

        $conversionRate = $leadsCreated > 0 ? ($leadsConverted / $leadsCreated) * 100 : 0;

        $couponsUsed = CouponUsage::whereBetween('used_at', [$dateFrom, $dateTo])
            ->count();

        $totalRevenue = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('total_amount');

        $totalDiscount = CouponUsage::whereBetween('used_at', [$dateFrom, $dateTo])
            ->sum('discount_amount');

        $avgOrderValue = $leadsConverted > 0 ? $totalRevenue / $leadsConverted : 0;

        $segmentCount = CustomerSegment::where('is_active', true)->count();
        $totalCustomers = User::count();

        return [
            'period' => [
                'from' => $dateFrom->toDateString(),
                'to' => $dateTo->toDateString(),
            ],
            'leads' => [
                'created' => $leadsCreated,
                'converted' => $leadsConverted,
                'conversion_rate' => round($conversionRate, 2),
            ],
            'coupons' => [
                'used' => $couponsUsed,
                'total_discount' => round($totalDiscount, 2),
            ],
            'revenue' => [
                'total' => round($totalRevenue, 2),
                'average_order_value' => round($avgOrderValue, 2),
            ],
            'segmentation' => [
                'active_segments' => $segmentCount,
                'total_customers' => $totalCustomers,
            ],
        ];
    }

    /**
     * Get lead analytics
     */
    public function getLeadAnalytics(array $filters = []): array
    {
        $dateFrom = isset($filters['date_from']) ? Carbon::parse($filters['date_from']) : now()->subMonths(1);
        $dateTo = isset($filters['date_to']) ? Carbon::parse($filters['date_to']) : now();

        $query = Lead::whereBetween('created_at', [$dateFrom, $dateTo]);

        $total = (clone $query)->count();
        $byStatus = (clone $query)->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->pluck('count', 'status')
            ->toArray();

        $byTemperature = (clone $query)->groupBy('temperature')
            ->selectRaw('temperature, count(*) as count')
            ->pluck('count', 'temperature')
            ->toArray();

        $bySource = (clone $query)->groupBy('source')
            ->selectRaw('source, count(*) as count')
            ->pluck('count', 'source')
            ->toArray();

        $avgScore = (clone $query)->avg('score') ?? 0;
        $avgEstimatedValue = (clone $query)->avg('estimated_value') ?? 0;

        return [
            'total_leads' => $total,
            'by_status' => $byStatus,
            'by_temperature' => $byTemperature,
            'by_source' => $bySource,
            'average_score' => round($avgScore, 2),
            'average_estimated_value' => round($avgEstimatedValue, 2),
        ];
    }

    /**
     * Get coupon analytics
     */
    public function getCouponAnalytics(array $filters = []): array
    {
        $dateFrom = isset($filters['date_from']) ? Carbon::parse($filters['date_from']) : now()->subMonths(1);
        $dateTo = isset($filters['date_to']) ? Carbon::parse($filters['date_to']) : now();

        $coupons = Coupon::whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('usages')
            ->get();

        $totalCoupons = $coupons->count();
        $totalUsages = $coupons->sum(function ($coupon) {
            return $coupon->usages->count();
        });

        $totalDiscountGiven = CouponUsage::whereBetween('used_at', [$dateFrom, $dateTo])
            ->sum('discount_amount')
            ?? 0;

        $byType = Coupon::whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('type')
            ->selectRaw('type, count(*) as count, sum(usage_count) as total_usage')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->type,
                    'count' => $item->count,
                    'total_usage' => $item->total_usage,
                ];
            })
            ->toArray();

        return [
            'total_coupons' => $totalCoupons,
            'total_usages' => $totalUsages,
            'total_discount_given' => round($totalDiscountGiven, 2),
            'by_type' => $byType,
            'usage_rate' => $totalCoupons > 0 ? round(($totalUsages / $totalCoupons) * 100, 2) : 0,
        ];
    }

    /**
     * Get segment analytics
     */
    public function getSegmentAnalytics(array $filters = []): array
    {
        $segments = CustomerSegment::where('is_active', true)->with('customers')->get();

        $staticSegments = $segments->where('type', 'static')->count();
        $dynamicSegments = $segments->where('type', 'dynamic')->count();

        $totalCustomersInSegments = $segments->sum(function ($segment) {
            return $segment->customers->count();
        });

        $avgSegmentSize = $staticSegments + $dynamicSegments > 0
            ? $totalCustomersInSegments / ($staticSegments + $dynamicSegments)
            : 0;

        $largestSegment = $segments->sortByDesc(function ($segment) {
            return $segment->customers->count();
        })->first();

        $segmentDetails = $segments->map(function ($segment) {
            return [
                'id' => $segment->id,
                'name' => $segment->name,
                'type' => $segment->type,
                'customer_count' => $segment->customers->count(),
                'created_at' => $segment->created_at,
            ];
        })->toArray();

        return [
            'total_segments' => $segments->count(),
            'static_segments' => $staticSegments,
            'dynamic_segments' => $dynamicSegments,
            'total_customers_in_segments' => $totalCustomersInSegments,
            'average_segment_size' => round($avgSegmentSize, 0),
            'largest_segment' => [
                'name' => $largestSegment?->name ?? null,
                'size' => $largestSegment?->customers->count() ?? 0,
            ],
            'segments' => $segmentDetails,
        ];
    }

    /**
     * Get customer lifetime value analysis
     */
    public function getCustomerLTV(array $filters = []): array
    {
        // Use a more efficient query to get LTV and order metrics per user
        $ltvData = DB::table('users')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->select('users.id', 'users.name', 'users.email')
            ->selectRaw('COALESCE(SUM(orders.total_amount), 0) as ltv')
            ->selectRaw('COUNT(orders.id) as order_count')
            ->selectRaw('CASE WHEN COUNT(orders.id) > 0 THEN SUM(orders.total_amount) / COUNT(orders.id) ELSE 0 END as avg_order_value')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('ltv')
            ->get();

        $avgLTV = $ltvData->avg('ltv') ?? 0;
        $totalLTV = $ltvData->sum('ltv') ?? 0;

        $segmentedByValue = [
            'vip' => $ltvData->where('ltv', '>=', $avgLTV * 2)->count(),
            'high' => $ltvData->where('ltv', '>=', $avgLTV)->where('ltv', '<', $avgLTV * 2)->count(),
            'medium' => $ltvData->where('ltv', '>=', $avgLTV / 2)->where('ltv', '<', $avgLTV)->count(),
            'low' => $ltvData->where('ltv', '<', $avgLTV / 2)->count(),
        ];

        return [
            'total_customers' => $ltvData->count(),
            'average_ltv' => round($avgLTV, 2),
            'total_ltv' => round($totalLTV, 2),
            'max_ltv' => round($ltvData->first()?->ltv ?? 0, 2),
            'segmentation' => $segmentedByValue,
            'top_customers' => $ltvData->take(10)->toArray(),
        ];
    }

    /**
     * Get cohort analysis
     */
    public function getCohortAnalysis(array $filters = []): array
    {
        $period = $filters['period'] ?? 'month'; // month, quarter, year

        $cohorts = User::groupBy(DB::raw("DATE_TRUNC('{$period}', created_at)"))
            ->selectRaw("DATE_TRUNC('{$period}', created_at) as cohort_date, count(*) as size")
            ->orderBy('cohort_date')
            ->get();

        return $cohorts->map(function ($cohort) {
            return [
                'cohort' => $cohort->cohort_date->toDateString(),
                'size' => $cohort->size,
                'retention' => $this->calculateCohortRetention($cohort->cohort_date),
            ];
        })->toArray();
    }

    /**
     * Calculate retention for a cohort
     */
    protected function calculateCohortRetention(Carbon $cohortDate): array
    {
        $cohortUsers = User::where('created_at', '>=', $cohortDate->startOfMonth())
            ->where('created_at', '<=', $cohortDate->endOfMonth())
            ->pluck('id');

        $retention = [];
        for ($i = 0; $i <= 11; $i++) {
            $checkDate = $cohortDate->copy()->addMonths($i);
            $activeUsers = Order::whereIn('user_id', $cohortUsers)
                ->whereBetween('created_at', [
                    $checkDate->startOfMonth(),
                    $checkDate->endOfMonth()
                ])
                ->distinct('user_id')
                ->count('user_id');

            $retention[$i] = $cohortUsers->count() > 0
                ? round(($activeUsers / $cohortUsers->count()) * 100, 2)
                : 0;
        }

        return $retention;
    }

    /**
     * Get conversion funnel
     */
    public function getConversionFunnel(): array
    {
        $leads = Lead::count();
        $contacted = Lead::where('status', '!=', Lead::STATUS_NEW)->count();
        $qualified = Lead::where('status', Lead::STATUS_QUALIFIED)->count();
        $converted = Lead::where('status', Lead::STATUS_CONVERTED)->count();

        return [
            'total_leads' => $leads,
            'contacted' => [
                'count' => $contacted,
                'rate' => $leads > 0 ? round(($contacted / $leads) * 100, 2) : 0,
            ],
            'qualified' => [
                'count' => $qualified,
                'rate' => $leads > 0 ? round(($qualified / $leads) * 100, 2) : 0,
            ],
            'converted' => [
                'count' => $converted,
                'rate' => $leads > 0 ? round(($converted / $leads) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get ROI analysis
     */
    public function getROI(array $filters = []): array
    {
        $dateFrom = isset($filters['date_from']) ? Carbon::parse($filters['date_from']) : now()->subMonths(1);
        $dateTo = isset($filters['date_to']) ? Carbon::parse($filters['date_to']) : now();

        $revenue = Order::whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum('total_amount') ?? 0;

        $discountGiven = CouponUsage::whereBetween('used_at', [$dateFrom, $dateTo])
            ->sum('discount_amount') ?? 0;

        $netRevenue = $revenue - $discountGiven;

        // Assume marketing costs (this should ideally come from another table)
        $estimatedMarketingCost = $revenue * 0.15; // 15% of revenue

        $roi = $estimatedMarketingCost > 0
            ? (($netRevenue / $estimatedMarketingCost) * 100) - 100
            : 0;

        return [
            'period' => [
                'from' => $dateFrom->toDateString(),
                'to' => $dateTo->toDateString(),
            ],
            'revenue' => round($revenue, 2),
            'discount_given' => round($discountGiven, 2),
            'net_revenue' => round($netRevenue, 2),
            'estimated_marketing_cost' => round($estimatedMarketingCost, 2),
            'roi_percentage' => round($roi, 2),
        ];
    }

    /**
     * Export analytics report
     */
    public function exportReport(string $type = 'full', array $filters = [])
    {
        $data = [];

        if ($type === 'full' || $type === 'dashboard') {
            $data['dashboard'] = $this->getDashboardSummary($filters);
        }

        if ($type === 'full' || $type === 'leads') {
            $data['leads'] = $this->getLeadAnalytics($filters);
        }

        if ($type === 'full' || $type === 'coupons') {
            $data['coupons'] = $this->getCouponAnalytics($filters);
        }

        if ($type === 'full' || $type === 'segments') {
            $data['segments'] = $this->getSegmentAnalytics($filters);
        }

        if ($type === 'full' || $type === 'ltv') {
            $data['ltv'] = $this->getCustomerLTV($filters);
        }

        if ($type === 'full' || $type === 'roi') {
            $data['roi'] = $this->getROI($filters);
        }

        return $data;
    }
}
