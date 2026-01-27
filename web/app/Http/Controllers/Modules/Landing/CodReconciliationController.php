<?php

namespace App\Http\Controllers\Modules\Landing;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Landing\CodReconciliationStoreRequest;
use App\Http\Requests\Modules\Landing\CodReconciliationItemUpdateRequest;
use App\Services\Landing\CodReconciliationService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CodReconciliationController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private CodReconciliationService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['shipping_partner', 'status']);
            $reconciliations = $this->service->getAll($filters, $request->input('per_page', 15));

            return $this->successResponse([
                'items' => $reconciliations->items(),
                'meta' => [
                    'current_page' => $reconciliations->currentPage(),
                    'last_page' => $reconciliations->lastPage(),
                    'total' => $reconciliations->total(),
                ],
            ]);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $reconciliation = $this->service->getById($id);
            return $this->successResponse($reconciliation);
        } catch (Exception $e) {
            return $this->notFoundResponse('not_found');
        }
    }

    public function store(CodReconciliationStoreRequest $request): JsonResponse
    {
        try {
            $reconciliation = $this->service->create($request->validated());
            return $this->createdResponse($reconciliation, 'reconciliation_created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function updateItems(int $id, CodReconciliationItemUpdateRequest $request): JsonResponse
    {
        try {
            $reconciliation = $this->service->updateItems($id, $request->validated()['items']);
            return $this->updatedResponse($reconciliation, 'updated');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function reconcile(int $id): JsonResponse
    {
        try {
            $reconciliation = $this->service->reconcile($id);
            return $this->successResponse($reconciliation, 'reconciliation_completed');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return $this->deletedResponse('reconciliation_deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function shippingPartners(): JsonResponse
    {
        return $this->successResponse($this->service->getShippingPartners());
    }
}




