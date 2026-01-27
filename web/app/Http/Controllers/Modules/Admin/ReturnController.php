<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Admin\ReturnStoreRequest;
use App\Http\Requests\Modules\Admin\ReturnReceiveItemRequest;
use App\Services\Admin\ReturnService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ReturnController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private ReturnService $returnService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'type']);
            $returns = $this->returnService->getAll($filters, $request->input('per_page', 15));

            return $this->paginatedResponse($returns);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $return = $this->returnService->getById($id);
            return $this->successResponse($return);
        } catch (Exception $e) {
            return $this->notFoundResponse('not_found');
        }
    }

    public function store(ReturnStoreRequest $request): JsonResponse
    {
        try {
            $return = $this->returnService->create($request->validated());
            return $this->createdResponse($return, 'rma_created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $refundAmount = $request->input('refund_amount', 0);
            $return = $this->returnService->approve($id, $refundAmount);
            return $this->successResponse($return, 'rma_approved');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $reason = $request->input('reason', '');
            $return = $this->returnService->reject($id, $reason);
            return $this->successResponse($return, 'rma_rejected');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function receiveItems(int $id, ReturnReceiveItemRequest $request): JsonResponse
    {
        try {
            $return = $this->returnService->receiveItems($id, $request->validated()['items']);
            return $this->successResponse($return, 'rma_items_received');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $return = $this->returnService->complete($id, $warehouseId);
            return $this->successResponse($return, 'rma_completed');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $return = $this->returnService->cancel($id);
            return $this->successResponse($return, 'rma_cancelled');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}



