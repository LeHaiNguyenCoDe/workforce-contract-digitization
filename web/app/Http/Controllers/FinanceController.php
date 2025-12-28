<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\FinanceTransaction;
use App\Http\Requests\FinanceTransactionRequest;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(
        private FinanceService $financeService
    ) {
    }

    /**
     * Get all funds
     */
    public function getFunds(): JsonResponse
    {
        $funds = Fund::where('is_active', true)->get();
        return response()->json([
            'status' => 'success',
            'data' => $funds,
        ]);
    }

    /**
     * Get transactions with filters
     */
    public function getTransactions(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        $transactions = $this->financeService->getTransactions($request->all(), $perPage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Create transaction (receipt or payment)
     */
    public function createTransaction(FinanceTransactionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            if ($data['type'] === 'receipt') {
                $transaction = $this->financeService->recordReceipt($data);
            } else {
                $transaction = $this->financeService->recordPayment($data);
            }

            return response()->json([
                'status' => 'success',
                'message' => $request->type === 'receipt' ? 'Ghi nhận thu tiền thành công' : 'Ghi nhận chi tiền thành công',
                'data' => $transaction,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get finance summary
     */
    public function getSummary(Request $request): JsonResponse
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        $summary = $this->financeService->getSummary($fromDate, $toDate);

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }
}
