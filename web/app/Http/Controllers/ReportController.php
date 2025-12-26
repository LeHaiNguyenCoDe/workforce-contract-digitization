<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    /**
     * Get P&L Report
     */
    public function pnl(Request $request): JsonResponse
    {
        try {
            $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
            $toDate = $request->input('to_date', now()->toDateString());
            $warehouseId = $request->input('warehouse_id');

            $report = $this->reportService->getPnLReport($fromDate, $toDate, $warehouseId);
            return response()->json(['status' => 'success', 'data' => $report]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Sales Report
     */
    public function sales(Request $request): JsonResponse
    {
        try {
            $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
            $toDate = $request->input('to_date', now()->toDateString());
            $warehouseId = $request->input('warehouse_id');

            $report = $this->reportService->getSalesReport($fromDate, $toDate, $warehouseId);
            return response()->json(['status' => 'success', 'data' => $report]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Inventory Report
     */
    public function inventory(Request $request): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');

            $report = $this->reportService->getInventoryReport($warehouseId);
            return response()->json(['status' => 'success', 'data' => $report]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
