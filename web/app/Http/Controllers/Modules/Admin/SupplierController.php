<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\SupplierStoreRequest;
use App\Http\Requests\Modules\Admin\SupplierUpdateRequest;
use App\Services\Admin\SupplierService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private SupplierService $supplierService
    ) {
    }

    public function index(): JsonResponse
    {
        try {
            $suppliers = $this->supplierService->getAll();
            return $this->successResponse($suppliers);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function store(SupplierStoreRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierService->create($request->validated());
            return $this->createdResponse($supplier, 'supplier_created');
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), null, 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $supplier = $this->supplierService->getById($id);
            if (!$supplier) {
                return $this->notFoundResponse('supplier_not_found');
            }
            return $this->successResponse($supplier);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }

    public function update(int $id, SupplierUpdateRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierService->update($id, $request->validated());
            return $this->updatedResponse($supplier, 'supplier_updated');
        } catch (\Exception $ex) {
            return $this->errorResponse($ex->getMessage(), null, 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->supplierService->delete($id);
            return $this->deletedResponse('supplier_deleted');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}


