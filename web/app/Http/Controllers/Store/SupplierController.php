<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\SupplierStoreRequest;
use App\Http\Requests\Store\SupplierUpdateRequest;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierService $supplierService
    ) {
    }

    /**
     * Get all suppliers
     */
    public function index(): JsonResponse
    {
        try {
            $suppliers = $this->supplierService->getAll();
            return response()->json([
                'status' => 'success',
                'data' => $suppliers,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching suppliers',
            ], 500);
        }
    }

    /**
     * Create supplier
     */
    public function store(SupplierStoreRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierService->create($request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier created',
                'data' => $supplier,
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 422);
        }
    }

    /**
     * Get supplier details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $supplier = $this->supplierService->getById($id);
            if (!$supplier) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Supplier not found',
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => $supplier,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching supplier details',
            ], 500);
        }
    }

    /**
     * Update supplier
     */
    public function update(int $id, SupplierUpdateRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierService->update($id, $request->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier updated',
                'data' => $supplier,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete supplier
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->supplierService->delete($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Supplier deleted',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while deleting supplier',
            ], 500);
        }
    }
}
