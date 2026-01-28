<?php

namespace App\Services\Marketing;

use App\Models\MarketingKpi;
use App\Models\CustomerValueTracking;
use App\Models\AttributionTouchpoint;
use App\Models\MarketingSource;
use App\Models\EmailCampaign;
use Illuminate\Support\Facades\DB;

class MarketingAnalyticsService
{
    /**
     * Get marketing dashboard overview
     */
    public function getDashboardOverview(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();

        $kpis = MarketingKpi::where('channel', MarketingKpi::CHANNEL_ALL)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->get();

        return [
            'total_sends' => $kpis->sum('total_sends'),
            'total_opens' => $kpis->sum('total_opens'),
            'total_clicks' => $kpis->sum('total_clicks'),
            'total_conversions' => $kpis->sum('total_conversions'),
            'total_revenue' => $kpis->sum('total_revenue'),
            'total_cost' => $kpis->sum('total_cost'),
            'avg_open_rate' => $kpis->avg('open_rate'),
            'avg_click_rate' => $kpis->avg('click_rate'),
            'avg_conversion_rate' => $kpis->avg('conversion_rate'),
            'roi' => $kpis->sum('total_cost') > 0
                ? round((($kpis->sum('total_revenue') - $kpis->sum('total_cost')) / $kpis->sum('total_cost')) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get KPIs by channel
     */
    public function getKpisByChannel(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();

        $channels = [
            MarketingKpi::CHANNEL_EMAIL,
            MarketingKpi::CHANNEL_SMS,
            MarketingKpi::CHANNEL_PUSH,
            MarketingKpi::CHANNEL_SOCIAL
        ];

        $result = [];

        foreach ($channels as $channel) {
            $kpis = MarketingKpi::where('channel', $channel)
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->get();

            $result[$channel] = [
                'total_sends' => $kpis->sum('total_sends'),
                'total_conversions' => $kpis->sum('total_conversions'),
                'total_revenue' => $kpis->sum('total_revenue'),
                'avg_open_rate' => round($kpis->avg('open_rate'), 2),
                'avg_conversion_rate' => round($kpis->avg('conversion_rate'), 2),
                'roi' => $kpis->sum('total_cost') > 0
                    ? round((($kpis->sum('total_revenue') - $kpis->sum('total_cost')) / $kpis->sum('total_cost')) * 100, 2)
                    : 0,
            ];
        }

        return $result;
    }

    /**
     * Get campaign performance
     */
    public function getCampaignPerformance(int $campaignId): array
    {
        $campaign = EmailCampaign::findOrFail($campaignId);

        $kpis = MarketingKpi::where('campaign_id', $campaignId)->get();

        return [
            'campaign' => $campaign,
            'total_sends' => $kpis->sum('total_sends'),
            'total_opens' => $kpis->sum('total_opens'),
            'total_clicks' => $kpis->sum('total_clicks'),
            'total_conversions' => $kpis->sum('total_conversions'),
            'total_revenue' => $kpis->sum('total_revenue'),
            'total_cost' => $kpis->sum('total_cost'),
            'open_rate' => round($kpis->avg('open_rate'), 2),
            'click_rate' => round($kpis->avg('click_rate'), 2),
            'conversion_rate' => round($kpis->avg('conversion_rate'), 2),
            'roi' => $kpis->sum('total_cost') > 0
                ? round((($kpis->sum('total_revenue') - $kpis->sum('total_cost')) / $kpis->sum('total_cost')) * 100, 2)
                : 0,
            'revenue_per_send' => $kpis->sum('total_sends') > 0
                ? round($kpis->sum('total_revenue') / $kpis->sum('total_sends'), 2)
                : 0,
        ];
    }

    /**
     * Calculate CLV for all customers
     */
    public function calculateAllCLV(): void
    {
        CustomerValueTracking::chunk(100, function ($trackings) {
            foreach ($trackings as $tracking) {
                $tracking->calculate();
            }
        });
    }

    /**
     * Get top customers by CLV
     */
    public function getTopCustomersByCLV(int $limit = 20)
    {
        return CustomerValueTracking::with('user')
            ->orderBy('clv_predicted', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get churned customers
     */
    public function getChurnedCustomers(int $perPage = 20)
    {
        return CustomerValueTracking::with('user')
            ->churned()
            ->orderBy('last_order_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get at-risk customers
     */
    public function getAtRiskCustomers(int $perPage = 20)
    {
        return CustomerValueTracking::with('user')
            ->atRisk()
            ->orderBy('churn_probability', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get attribution report
     */
    public function getAttributionReport(array $filters = []): array
    {
        $query = MarketingSource::query();

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $sources = $query->orderBy('total_revenue', 'desc')->get();

        $totalRevenue = $sources->sum('total_revenue');
        $totalCost = $sources->sum('total_cost');

        return [
            'sources' => $sources->map(function ($source) use ($totalRevenue) {
                return [
                    'name' => $source->name,
                    'type' => $source->type,
                    'sessions' => $source->total_sessions,
                    'conversions' => $source->total_conversions,
                    'revenue' => $source->total_revenue,
                    'cost' => $source->cost_spent,
                    'conversion_rate' => $source->conversion_rate,
                    'roi' => $source->roi,
                    'cpa' => $source->cpa,
                    'roas' => $source->getRoas(),
                    'revenue_share' => $totalRevenue > 0
                        ? round(($source->total_revenue / $totalRevenue) * 100, 2)
                        : 0,
                ];
            }),
            'totals' => [
                'revenue' => $totalRevenue,
                'cost' => $totalCost,
                'roi' => $totalCost > 0
                    ? round((($totalRevenue - $totalCost) / $totalCost) * 100, 2)
                    : 0,
            ],
        ];
    }

    /**
     * Get trends (daily metrics over time)
     */
    public function getTrends(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();
        $channel = $filters['channel'] ?? MarketingKpi::CHANNEL_ALL;

        $kpis = MarketingKpi::where('channel', $channel)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->orderBy('date')
            ->get();

        return $kpis->map(function ($kpi) {
            return [
                'date' => $kpi->date->format('Y-m-d'),
                'sends' => $kpi->total_sends,
                'opens' => $kpi->total_opens,
                'clicks' => $kpi->total_clicks,
                'conversions' => $kpi->total_conversions,
                'revenue' => $kpi->total_revenue,
                'open_rate' => $kpi->open_rate,
                'click_rate' => $kpi->click_rate,
                'conversion_rate' => $kpi->conversion_rate,
            ];
        })->toArray();
    }

    /**
     * Get cohort analysis
     */
    public function getCohortAnalysis(string $period = 'month'): array
    {
        // Group customers by first order month
        $cohorts = DB::table('customer_value_tracking')
            ->select(
                DB::raw('DATE_FORMAT(first_order_date, "%Y-%m") as cohort'),
                DB::raw('COUNT(*) as customer_count'),
                DB::raw('SUM(total_spent) as total_revenue'),
                DB::raw('AVG(total_orders) as avg_orders'),
                DB::raw('AVG(clv_predicted) as avg_clv')
            )
            ->whereNotNull('first_order_date')
            ->groupBy('cohort')
            ->orderBy('cohort', 'desc')
            ->limit(12)
            ->get();

        return $cohorts->map(function ($cohort) {
            return [
                'cohort' => $cohort->cohort,
                'customers' => $cohort->customer_count,
                'total_revenue' => round($cohort->total_revenue, 2),
                'avg_orders' => round($cohort->avg_orders, 2),
                'avg_clv' => round($cohort->avg_clv, 2),
                'avg_revenue_per_customer' => $cohort->customer_count > 0
                    ? round($cohort->total_revenue / $cohort->customer_count, 2)
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Calculate and store daily KPIs
     */
    public function calculateDailyKPIs(string $date): void
    {
        $date = $date ?? now()->subDay()->format('Y-m-d');

        // This would aggregate data from campaign_sends, orders, etc.
        // Simplified example:

        MarketingKpi::updateOrCreate(
            [
                'date' => $date,
                'channel' => MarketingKpi::CHANNEL_ALL,
                'campaign_id' => null,
            ],
            [
                'total_sends' => 0, // Would calculate from actual data
                'total_opens' => 0,
                'total_clicks' => 0,
                'total_conversions' => 0,
                'total_revenue' => 0,
                'total_cost' => 0,
                'calculated_at' => now(),
            ]
        );

        // Calculate rates
        $kpi = MarketingKpi::where('date', $date)
            ->where('channel', MarketingKpi::CHANNEL_ALL)
            ->first();

        if ($kpi) {
            $kpi->calculateRates();
        }
    }

    /**
     * Get marketing funnel
     */
    public function getMarketingFunnel(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? now()->subDays(30);
        $dateTo = $filters['date_to'] ?? now();

        // Example funnel: Sends -> Opens -> Clicks -> Conversions
        $kpis = MarketingKpi::whereBetween('date', [$dateFrom, $dateTo])
            ->where('channel', '!=', MarketingKpi::CHANNEL_ALL)
            ->get();

        $sends = $kpis->sum('total_sends');
        $opens = $kpis->sum('total_opens');
        $clicks = $kpis->sum('total_clicks');
        $conversions = $kpis->sum('total_conversions');

        return [
            [
                'stage' => 'Gửi',
                'count' => $sends,
                'percentage' => 100,
            ],
            [
                'stage' => 'Mở',
                'count' => $opens,
                'percentage' => $sends > 0 ? round(($opens / $sends) * 100, 2) : 0,
            ],
            [
                'stage' => 'Click',
                'count' => $clicks,
                'percentage' => $sends > 0 ? round(($clicks / $sends) * 100, 2) : 0,
            ],
            [
                'stage' => 'Chuyển đổi',
                'count' => $conversions,
                'percentage' => $sends > 0 ? round(($conversions / $sends) * 100, 2) : 0,
            ],
        ];
    }
}
