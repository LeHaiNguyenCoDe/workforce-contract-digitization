<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'sometimes|string|in:active,inactive',
            ]);

            $supplier = $this->supplierService->create($validated);
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
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'status' => 'sometimes|string|in:active,inactive',
                'rating' => 'sometimes|numeric|min:0|max:5',
            ]);

            $supplier = $this->supplierService->update($id, $validated);
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
