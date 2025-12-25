<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\WarehouseStoreRequest;
use App\Http\Requests\Store\WarehouseUpdateRequest;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct(
        private WarehouseService $warehouseService
    ) {
    }

    /**
     * Get all warehouses
     */
    public function index(): JsonResponse
    {
        try {
            $warehouses = $this->warehouseService->getAll();

            return response()->json([
                'status' => 'success',
                'data' => $warehouses,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Create warehouse
     */
    public function store(WarehouseStoreRequest $request): JsonResponse
    {
        try {
            $warehouse = $this->warehouseService->create($request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Warehouse created',
                'data' => $warehouse,
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get warehouse details
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        try {
            $warehouseData = $this->warehouseService->getById($warehouse->id);

            return response()->json([
                'status' => 'success',
                'data' => $warehouseData,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Update warehouse
     */
    public function update(Warehouse $warehouse, WarehouseUpdateRequest $request): JsonResponse
    {
        try {
            $warehouseData = $this->warehouseService->update($warehouse->id, $request->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Warehouse updated',
                'data' => $warehouseData,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Delete warehouse
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        try {
            $this->warehouseService->delete($warehouse->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Warehouse deleted',
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get stocks by warehouse
     */
    public function stocks(Warehouse $warehouse): JsonResponse
    {
        try {
            if (!$warehouse || !$warehouse->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Warehouse not found',
                ], 404);
            }

            $stocks = $this->warehouseService->getStocks($warehouse->id);

            return response()->json([
                'status' => 'success',
                'data' => $stocks,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Update stock for warehouse
     */
    public function updateStock(Warehouse $warehouse, Request $request): JsonResponse
    {
        try {
            if (!$warehouse || !$warehouse->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Warehouse not found',
                ], 404);
            }

            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'product_variant_id' => 'sometimes|integer|exists:product_variants,id',
                'quantity' => 'required|integer',
                'type' => 'required|string|in:in,out,adjust',
                'note' => 'sometimes|string',
            ]);

            $stock = $this->warehouseService->updateStock($warehouse->id, $request->only([
                'product_id',
                'product_variant_id',
                'quantity',
                'type',
                'note'
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Stock updated',
                'data' => $stock,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get stock movements for warehouse
     */
    public function stockMovements(Warehouse $warehouse, Request $request): JsonResponse
    {
        try {
            if (!$warehouse || !$warehouse->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Warehouse not found',
                ], 404);
            }

            $perPage = $request->query('per_page', 20);
            $movements = $this->warehouseService->getStockMovements($warehouse->id, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $movements,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get warehouse dashboard stats
     */
    public function dashboardStats(): JsonResponse
    {
        try {
            $stats = $this->warehouseService->getDashboardStats();

            return response()->json([
                'status' => 'success',
                'data' => $stats,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get quality checks
     */
    public function qualityChecks(): JsonResponse
    {
        try {
            $checks = $this->warehouseService->getQualityChecks();

            return response()->json([
                'status' => 'success',
                'data' => $checks,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Store quality check
     */
    public function storeQualityCheck(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'batch_number' => 'required|string|unique:quality_checks,batch_number',
                'product_id' => 'required|exists:products,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'check_date' => 'required|date',
                'status' => 'required|in:passed,failed,pending',
                'score' => 'required|integer|min:0|max:100',
                'notes' => 'nullable|string',
                'issues' => 'nullable|array'
            ]);

            $data = $request->all();
            $data['inspector_id'] = \Illuminate\Support\Facades\Auth::id() ?? 1; // Default to 1 if not auth

            $check = $this->warehouseService->storeQualityCheck($data);

            return response()->json([
                'status' => 'success',
                'data' => $check,
            ], 201);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Update quality check
     */
    public function updateQualityCheck(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'sometimes|in:passed,failed,pending',
                'score' => 'sometimes|integer|min:0|max:100',
                'notes' => 'nullable|string',
                'issues' => 'nullable|array'
            ]);

            $check = $this->warehouseService->updateQualityCheck($id, $request->all());

            return response()->json([
                'status' => 'success',
                'data' => $check,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete quality check
     */
    public function deleteQualityCheck(int $id): JsonResponse
    {
        try {
            $this->warehouseService->deleteQualityCheck($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Quality check deleted',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
}


