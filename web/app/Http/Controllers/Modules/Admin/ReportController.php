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
    ) {}

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
}



