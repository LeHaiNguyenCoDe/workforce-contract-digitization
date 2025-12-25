<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessLogicException;
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
     * BR-06.1: Chỉ hiển thị Available Inventory
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
            \Log::error('Get stocks error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'warehouse_id' => $warehouse->id ?? null,
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * BR-02.1: Tạo Inbound Batch
     */
    public function createInboundBatch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'supplier_id' => 'required|exists:suppliers,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
                'items.*.quantity_received' => 'required|integer|min:1',
                'notes' => 'nullable|string',
            ]);

            $batch = $this->warehouseService->createInboundBatch($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Inbound batch created successfully',
                'data' => $batch,
            ], 201);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $ex->errors(),
            ], 422);
        } catch (\Exception $ex) {
            \Log::error('Create inbound batch error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'request' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * BR-02.2: Nhận hàng (RECEIVED)
     */
    public function receiveInboundBatch(int $batchId, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'received_date' => 'nullable|date',
                'items' => 'nullable|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
                'items.*.quantity_received' => 'required|integer|min:0',
                'notes' => 'nullable|string',
            ]);

            $batch = $this->warehouseService->receiveInboundBatch($batchId, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Batch received successfully',
                'data' => $batch,
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Exception $ex) {
            \Log::error('Receive inbound batch error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'batch_id' => $batchId,
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get inbound batches
     */
    public function inboundBatches(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'warehouse_id']);
            $batches = $this->warehouseService->getInboundBatches($filters);

            return response()->json([
                'status' => 'success',
                'data' => $batches,
            ]);
        } catch (\Exception $ex) {
            \Log::error('Get inbound batches error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get inbound batch by ID
     */
    public function showInboundBatch(int $id): JsonResponse
    {
        try {
            $batch = $this->warehouseService->getInboundBatch($id);

            return response()->json([
                'status' => 'success',
                'data' => $batch,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * BR-03.1: Tạo Quality Check (chỉ trên Batch)
     */
    public function createQualityCheck(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'inbound_batch_id' => 'required|exists:inbound_batches,id',
                'product_id' => 'required|exists:products,id',
                'check_date' => 'nullable|date',
                'status' => 'required|in:pass,fail,partial',
                'score' => 'nullable|integer|min:0|max:100',
                'quantity_passed' => 'required|integer|min:0',
                'quantity_failed' => 'required|integer|min:0',
                'notes' => 'nullable|string',
                'issues' => 'nullable|array',
            ]);

            $qc = $this->warehouseService->createQualityCheck($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Quality check created successfully',
                'data' => $qc,
            ], 201);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $ex->errors(),
            ], 422);
        } catch (\Exception $ex) {
            \Log::error('Create quality check error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'request' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
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
     * BR-05.1, BR-05.2, BR-05.3: Điều chỉnh tồn kho
     */
    public function adjustStock(Warehouse $warehouse, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'product_variant_id' => 'sometimes|integer|exists:product_variants,id',
                'quantity' => 'required|integer|min:0',
                'available_quantity' => 'sometimes|integer|min:0',
                'reason' => 'required|string|min:3', // BR-05.2: Bắt buộc có lý do
                'note' => 'nullable|string',
            ]);

            $stock = $this->warehouseService->adjustStock($warehouse->id, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Stock adjusted successfully',
                'data' => $stock,
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $ex->errors(),
            ], 422);
        } catch (\Exception $ex) {
            \Log::error('Adjust stock error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'request' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * BR-06.1, BR-06.2, BR-06.3: Xuất kho
     */
    public function outboundStock(Warehouse $warehouse, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'product_variant_id' => 'sometimes|integer|exists:product_variants,id',
                'quantity' => 'required|integer|min:1',
                'reference_type' => 'nullable|string',
                'reference_id' => 'nullable|integer',
                'note' => 'nullable|string',
            ]);

            $stock = $this->warehouseService->outboundStock($warehouse->id, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Stock outbound successfully',
                'data' => $stock,
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $ex->errors(),
            ], 422);
        } catch (\Exception $ex) {
            \Log::error('Outbound stock error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'request' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
            ], 500);
        }
    }

    /**
     * Get inventory logs (BR-09.2)
     */
    public function inventoryLogs(Warehouse $warehouse, Request $request): JsonResponse
    {
        try {
            if (!$warehouse || !$warehouse->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Warehouse not found',
                ], 404);
            }

            $perPage = $request->query('per_page', 20);
            $logs = $this->warehouseService->getInventoryLogs($warehouse->id, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $logs,
            ]);
        } catch (NotFoundException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 404);
        } catch (\Exception $ex) {
            \Log::error('Get inventory logs error: ' . $ex->getMessage(), [
                'exception' => $ex,
                'warehouse_id' => $warehouse->id ?? null,
                'trace' => $ex->getTraceAsString(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage() ?: 'An error occurred while processing the request',
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
     * Update quality check (legacy - chỉ cho rollback)
     */
    public function updateQualityCheck(int $id, Request $request): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'sometimes|in:pass,fail,partial',
                'score' => 'sometimes|integer|min:0|max:100',
                'notes' => 'nullable|string',
                'issues' => 'nullable|array'
            ]);

            $check = $this->warehouseService->updateQualityCheck($id, $request->all());

            return response()->json([
                'status' => 'success',
                'data' => $check,
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete quality check (BR-09.1: Không được xóa)
     */
    public function deleteQualityCheck(int $id): JsonResponse
    {
        try {
            $this->warehouseService->deleteQualityCheck($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Quality check deleted',
            ]);
        } catch (BusinessLogicException $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 400);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], 500);
        }
    }
}
