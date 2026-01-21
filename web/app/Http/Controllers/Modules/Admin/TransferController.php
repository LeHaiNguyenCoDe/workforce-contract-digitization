<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Admin\TransferStoreRequest;
use App\Http\Requests\Modules\Admin\TransferReceiveRequest;
use App\Services\Admin\TransferService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class TransferController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private TransferService $transferService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['from_warehouse_id', 'to_warehouse_id', 'status']);
            $transfers = $this->transferService->getAll($filters, $request->input('per_page', 15));

            return $this->paginatedResponse($transfers);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->getById($id);
            return $this->successResponse($transfer);
        } catch (Exception $e) {
            return $this->notFoundResponse('not_found');
        }
    }

    public function store(TransferStoreRequest $request): JsonResponse
    {
        try {
            $transfer = $this->transferService->create($request->validated());
            return $this->createdResponse($transfer, 'transfer_created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function ship(int $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->ship($id);
            return $this->successResponse($transfer, 'transfer_shipped');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function receive(TransferReceiveRequest $request, int $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->receive($id, $request->validated()['items']);
            return $this->successResponse($transfer, 'transfer_received');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->cancel($id);
            return $this->successResponse($transfer, 'transfer_cancelled');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->transferService->delete($id);
            return $this->deletedResponse('transfer_deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}
