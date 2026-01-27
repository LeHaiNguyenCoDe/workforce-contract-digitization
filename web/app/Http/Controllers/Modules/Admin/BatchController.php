<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Admin\BatchStoreRequest;
use App\Http\Requests\Modules\Admin\BatchUpdateRequest;
use App\Services\Admin\BatchService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private BatchService $batchService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'product_id', 'warehouse_id', 'status', 'expiring_soon']);
            $perPage = $request->input('per_page', 15);

            $batches = $this->batchService->getAll($filters, $perPage);

            return $this->paginatedResponse($batches);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $batch = $this->batchService->getById($id);

            return $this->successResponse($batch);
        } catch (NotFoundException $e) {
            return $this->notFoundResponse('batch_not_found');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function store(BatchStoreRequest $request): JsonResponse
    {
        try {
            $batch = $this->batchService->create($request->validated());

            return $this->createdResponse($batch, 'batch_created');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function update(int $id, BatchUpdateRequest $request): JsonResponse
    {
        try {
            $batch = $this->batchService->update($id, $request->validated());

            return $this->updatedResponse($batch, 'batch_updated');
        } catch (NotFoundException $e) {
            return $this->notFoundResponse('batch_not_found');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->batchService->delete($id);

            return $this->deletedResponse('batch_deleted');
        } catch (NotFoundException $e) {
            return $this->notFoundResponse('batch_not_found');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function getProductBatches(Request $request, int $productId): JsonResponse
    {
        try {
            $warehouseId = $request->input('warehouse_id');
            $batches = $this->batchService->getAvailableBatches($productId, $warehouseId);

            return $this->successResponse($batches);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function expiringSoon(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days', 30);
            $summary = $this->batchService->getExpiringSoonSummary($days);

            return $this->successResponse($summary);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}
