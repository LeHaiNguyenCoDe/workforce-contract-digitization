<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Models\AccountReceivable;
use App\Models\AccountPayable;
use App\Http\Requests\Modules\Admin\DebtPaymentRequest;
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
        try {
            $receivables = $this->debtService->getReceivables($request->all());
            return $this->paginatedResponse($receivables);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function getReceivableSummary(): JsonResponse
    {
        try {
            $summary = $this->debtService->getARSummary();
            return $this->successResponse($summary);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function payReceivable(AccountReceivable $receivable, DebtPaymentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $amount = $validated['amount'];

            $transaction = $this->financeService->recordReceipt([
                'amount' => $amount,
                'fund_id' => $validated['fund_id'] ?? null,
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
        try {
            $payables = $this->debtService->getPayables($request->all());
            return $this->paginatedResponse($payables);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function getPayableSummary(): JsonResponse
    {
        try {
            $summary = $this->debtService->getAPSummary();
            return $this->successResponse($summary);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function payPayable(AccountPayable $payable, DebtPaymentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $amount = $validated['amount'];

            $transaction = $this->financeService->recordPayment([
                'amount' => $amount,
                'fund_id' => $validated['fund_id'] ?? null,
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
