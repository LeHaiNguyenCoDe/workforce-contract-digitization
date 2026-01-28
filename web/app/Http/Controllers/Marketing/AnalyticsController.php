<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {
    }

    /**
     * Get dashboard summary
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $summary = $this->analyticsService->getDashboardSummary($filters);

            return response()->json([
                'status' => 'success',
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead analytics
     */
    public function leads(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $analytics = $this->analyticsService->getLeadAnalytics($filters);

            return response()->json([
                'status' => 'success',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get coupon analytics
     */
    public function coupons(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $analytics = $this->analyticsService->getCouponAnalytics($filters);

            return response()->json([
                'status' => 'success',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get segment analytics
     */
    public function segments(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $analytics = $this->analyticsService->getSegmentAnalytics($filters);

            return response()->json([
                'status' => 'success',
                'data' => $analytics,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer LTV analysis
     */
    public function ltv(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $ltv = $this->analyticsService->getCustomerLTV($filters);

            return response()->json([
                'status' => 'success',
                'data' => $ltv,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cohort analysis
     */
    public function cohorts(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['period']);
            $cohorts = $this->analyticsService->getCohortAnalysis($filters);

            return response()->json([
                'status' => 'success',
                'data' => $cohorts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversion funnel
     */
    public function funnel(): JsonResponse
    {
        try {
            $funnel = $this->analyticsService->getConversionFunnel();

            return response()->json([
                'status' => 'success',
                'data' => $funnel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get ROI analysis
     */
    public function roi(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $roi = $this->analyticsService->getROI($filters);

            return response()->json([
                'status' => 'success',
                'data' => $roi,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export analytics report
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'full');
            $filters = $request->only(['date_from', 'date_to']);

            $report = $this->analyticsService->exportReport($type, $filters);

            return response()->json([
                'status' => 'success',
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
