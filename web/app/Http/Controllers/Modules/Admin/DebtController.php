<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Models\AccountReceivable;
use App\Models\AccountPayable;
use App\Services\Admin\DebtService;
use App\Services\Admin\FinanceService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private DebtService $debtService,
        private FinanceService $financeService
    ) {
    }

    public function getReceivables(Request $request): JsonResponse
    {
        $receivables = $this->debtService->getReceivables($request->all());

        return $this->successResponse([
            'items' => $receivables->items(),
            'meta' => [
                'current_page' => $receivables->currentPage(),
                'last_page' => $receivables->lastPage(),
                'total' => $receivables->total(),
            ],
        ]);
    }

    public function getReceivableSummary(): JsonResponse
    {
        $summary = $this->debtService->getARSummary();

        return $this->successResponse($summary);
    }

    public function payReceivable(AccountReceivable $receivable, Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'fund_id' => 'sometimes|exists:funds,id',
        ]);

        try {
            $amount = $request->input('amount');

            $transaction = $this->financeService->recordReceipt([
                'amount' => $amount,
                'fund_id' => $request->input('fund_id'),
                'reference_type' => 'ar_payment',
                'reference_id' => $receivable->id,
                'description' => "Thu công nợ {$receivable->ar_code}",
            ]);

            $receivable->recordPayment($amount);

            return $this->successResponse($receivable->fresh(), 'ar_payment_success');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function getPayables(Request $request): JsonResponse
    {
        $payables = $this->debtService->getPayables($request->all());

        return $this->successResponse([
            'items' => $payables->items(),
            'meta' => [
                'current_page' => $payables->currentPage(),
                'last_page' => $payables->lastPage(),
                'total' => $payables->total(),
            ],
        ]);
    }

    public function getPayableSummary(): JsonResponse
    {
        $summary = $this->debtService->getAPSummary();

        return $this->successResponse($summary);
    }

    public function payPayable(AccountPayable $payable, Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'fund_id' => 'sometimes|exists:funds,id',
        ]);

        try {
            $amount = $request->input('amount');

            $transaction = $this->financeService->recordPayment([
                'amount' => $amount,
                'fund_id' => $request->input('fund_id'),
                'reference_type' => 'ap_payment',
                'reference_id' => $payable->id,
                'description' => "Trả công nợ NCC {$payable->ap_code}",
            ]);

            $payable->recordPayment($amount);

            return $this->successResponse($payable->fresh(), 'ap_payment_success');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}



