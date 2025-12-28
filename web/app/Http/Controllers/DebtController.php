<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\AccountPayable;
use App\Services\DebtService;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function __construct(
        private DebtService $debtService,
        private FinanceService $financeService
    ) {
    }

    /**
     * Get all receivables (AR)
     */
    public function getReceivables(Request $request): JsonResponse
    {
        $receivables = $this->debtService->getReceivables($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $receivables->items(),
            'meta' => [
                'current_page' => $receivables->currentPage(),
                'last_page' => $receivables->lastPage(),
                'total' => $receivables->total(),
            ],
        ]);
    }

    /**
     * Get AR summary
     */
    public function getReceivableSummary(): JsonResponse
    {
        $summary = $this->debtService->getARSummary();

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    /**
     * Pay on AR (collect payment from customer)
     */
    public function payReceivable(AccountReceivable $receivable, Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'fund_id' => 'sometimes|exists:funds,id',
        ]);

        try {
            $amount = $request->input('amount');

            // Record payment in fund
            $transaction = $this->financeService->recordReceipt([
                'amount' => $amount,
                'fund_id' => $request->input('fund_id'),
                'reference_type' => 'ar_payment',
                'reference_id' => $receivable->id,
                'description' => "Thu công nợ {$receivable->ar_code}",
            ]);

            // Update AR
            $receivable->recordPayment($amount);

            return response()->json([
                'status' => 'success',
                'message' => 'Thu tiền công nợ thành công',
                'data' => $receivable->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get all payables (AP)
     */
    public function getPayables(Request $request): JsonResponse
    {
        $payables = $this->debtService->getPayables($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $payables->items(),
            'meta' => [
                'current_page' => $payables->currentPage(),
                'last_page' => $payables->lastPage(),
                'total' => $payables->total(),
            ],
        ]);
    }

    /**
     * Get AP summary
     */
    public function getPayableSummary(): JsonResponse
    {
        $summary = $this->debtService->getAPSummary();

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    /**
     * Pay on AP (pay to supplier)
     */
    public function payPayable(AccountPayable $payable, Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'fund_id' => 'sometimes|exists:funds,id',
        ]);

        try {
            $amount = $request->input('amount');

            // Record payment
            $transaction = $this->financeService->recordPayment([
                'amount' => $amount,
                'fund_id' => $request->input('fund_id'),
                'reference_type' => 'ap_payment',
                'reference_id' => $payable->id,
                'description' => "Trả công nợ NCC {$payable->ap_code}",
            ]);

            // Update AP
            $payable->recordPayment($amount);

            return response()->json([
                'status' => 'success',
                'message' => 'Thanh toán công nợ NCC thành công',
                'data' => $payable->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
