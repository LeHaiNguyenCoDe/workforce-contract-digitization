<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Admin\FinanceTransactionRequest;
use App\Services\Admin\FinanceService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private FinanceService $financeService
    ) {
    }

    public function getFunds(): JsonResponse
    {
        try {
            $funds = $this->financeService->getFunds();
            return $this->successResponse($funds);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $filters = $request->only(['type', 'fund_id', 'from_date', 'to_date']);
            $transactions = $this->financeService->getTransactions($filters, $perPage);

            return $this->paginatedResponse($transactions);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function createTransaction(FinanceTransactionRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            if ($data['type'] === 'receipt') {
                $transaction = $this->financeService->recordReceipt($data);
                return $this->createdResponse($transaction, 'receipt_recorded');
            } else {
                $transaction = $this->financeService->recordPayment($data);
                return $this->createdResponse($transaction, 'payment_recorded');
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function getSummary(Request $request): JsonResponse
    {
        try {
            $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
            $toDate = $request->input('to_date', now()->toDateString());

            $summary = $this->financeService->getSummary($fromDate, $toDate);

            return $this->successResponse($summary);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}
