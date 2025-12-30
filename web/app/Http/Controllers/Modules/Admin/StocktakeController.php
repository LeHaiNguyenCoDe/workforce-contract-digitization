<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Admin\StocktakeStoreRequest;
use App\Http\Requests\Modules\Admin\StocktakeItemUpdateRequest;
use App\Services\Admin\StocktakeService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class StocktakeController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private StocktakeService $stocktakeService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['warehouse_id', 'status']);
            $perPage = $request->input('per_page', 15);

            $stocktakes = $this->stocktakeService->getAll($filters, $perPage);

            return $this->successResponse([
                'items' => $stocktakes->items(),
                'meta' => [
                    'current_page' => $stocktakes->currentPage(),
                    'last_page' => $stocktakes->lastPage(),
                    'per_page' => $stocktakes->perPage(),
                    'total' => $stocktakes->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->getById($id);

            return $this->successResponse($stocktake);
        } catch (Exception $e) {
            return $this->notFoundResponse('not_found');
        }
    }

    public function store(StocktakeStoreRequest $request): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->create($request->validated());

            return $this->createdResponse($stocktake, 'stocktake_created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function start(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->start($id);

            return $this->successResponse($stocktake, 'stocktake_started');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function updateItems(int $id, StocktakeItemUpdateRequest $request): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->updateItems($id, $request->validated()['items']);

            return $this->updatedResponse($stocktake, 'stocktake_items_updated');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function complete(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->complete($id);

            return $this->successResponse($stocktake, 'stocktake_completed');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function approve(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->approve($id);

            return $this->successResponse($stocktake, 'stocktake_approved');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function cancel(int $id): JsonResponse
    {
        try {
            $stocktake = $this->stocktakeService->cancel($id);

            return $this->successResponse($stocktake, 'stocktake_cancelled');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->stocktakeService->delete($id);

            return $this->deletedResponse('stocktake_deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}



