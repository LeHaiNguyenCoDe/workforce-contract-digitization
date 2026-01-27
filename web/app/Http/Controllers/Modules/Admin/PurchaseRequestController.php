<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Admin\PurchaseRequestStoreRequest;
use App\Http\Requests\Modules\Admin\PurchaseRequestUpdateRequest;
use App\Services\Admin\PurchaseRequestService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class PurchaseRequestController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private PurchaseRequestService $purchaseRequestService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status', 'source', 'warehouse_id', 'supplier_id']);
            $perPage = $request->input('per_page', 15);

            $requests = $this->purchaseRequestService->getAll($filters, $perPage);

            return $this->paginatedResponse($requests);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $request = $this->purchaseRequestService->getById($id);

            return $this->successResponse($request);
        } catch (Exception $e) {
            return $this->notFoundResponse('not_found');
        }
    }

    public function store(PurchaseRequestStoreRequest $request): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->create($request->validated());

            return $this->createdResponse($pr, 'pr_created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function update(int $id, PurchaseRequestUpdateRequest $request): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->update($id, $request->validated());

            return $this->updatedResponse($pr, 'pr_updated');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function approve(int $id): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->approve($id);

            return $this->successResponse($pr, 'pr_approved');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $reason = $request->input('reason');
            $pr = $this->purchaseRequestService->reject($id, $reason);

            return $this->successResponse($pr, 'pr_rejected');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function markOrdered(int $id): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->markAsOrdered($id);

            return $this->successResponse($pr, 'pr_ordered');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function complete(int $id): JsonResponse
    {
        try {
            $pr = $this->purchaseRequestService->complete($id);

            return $this->successResponse($pr, 'pr_completed');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $this->purchaseRequestService->cancel($id);

            return $this->deletedResponse('pr_cancelled');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->purchaseRequestService->delete($id);

            return $this->deletedResponse('pr_deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function summary(): JsonResponse
    {
        try {
            $summary = $this->purchaseRequestService->getSummary();

            return $this->successResponse($summary);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}



