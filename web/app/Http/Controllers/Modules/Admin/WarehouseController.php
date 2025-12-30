<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessLogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Modules\Admin\WarehouseStoreRequest;
use App\Http\Requests\Modules\Admin\WarehouseUpdateRequest;
use App\Http\Requests\Modules\Admin\InboundBatchRequest;
use App\Http\Requests\Modules\Admin\ReceiveBatchRequest;
use App\Http\Requests\Modules\Admin\QualityCheckRequest;
use App\Http\Requests\Modules\Admin\StockAdjustmentRequest;
use App\Http\Requests\Modules\Admin\StockOutboundRequest;
use App\Http\Requests\Modules\Admin\InboundReceiptRequest;
use App\Http\Requests\Modules\Admin\OutboundReceiptRequest;
use App\Models\Warehouse;
use App\Services\Admin\WarehouseService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    use StoreApiResponse;

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

            return $this->successResponse($warehouses);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while processing the request', $ex);
        }
    }

    /**
     * Create warehouse
     */
    public function store(WarehouseStoreRequest $request): JsonResponse
    {
        try {
            $warehouse = $this->warehouseService->create($request->validated());

            return $this->createdResponse($warehouse, 'Warehouse created');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while processing the request', $ex);
        }
    }

    /**
     * Get warehouse details
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        try {
            $warehouseData = $this->warehouseService->getById($warehouse->id);

            return $this->successResponse($warehouseData);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while processing the request', $ex);
        }
    }

    /**
     * Update warehouse
     */
    public function update(Warehouse $warehouse, WarehouseUpdateRequest $request): JsonResponse
    {
        try {
            $warehouseData = $this->warehouseService->update($warehouse->id, $request->validated());

            return $this->updatedResponse($warehouseData, 'Warehouse updated');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while processing the request', $ex);
        }
    }

    /**
     * Delete warehouse
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        try {
            $this->warehouseService->delete($warehouse->id);

            return $this->deletedResponse('Warehouse deleted');
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while processing the request', $ex);
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
                return $this->notFoundResponse('Warehouse not found');
            }

            $stocks = $this->warehouseService->getStocks($warehouse->id);

            return $this->successResponse($stocks);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
        }
    }

    /**
     * BR-02.1: Tạo Inbound Batch
     */
    public function createInboundBatch(InboundBatchRequest $request): JsonResponse
    {
        try {
            $batch = $this->warehouseService->createInboundBatch($request->validated());

            return $this->createdResponse($batch, 'Inbound batch created successfully');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return $this->validationErrorResponse($ex->errors());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
        }
    }

    /**
     * BR-02.2: Nhận hàng (RECEIVED)
     */
    public function receiveInboundBatch(int $batchId, ReceiveBatchRequest $request): JsonResponse
    {
        try {
            $batch = $this->warehouseService->receiveInboundBatch($batchId, $request->validated());

            return $this->successResponse($batch, 'Batch received successfully');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
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

            return $this->successResponse($batches);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
        }
    }

    /**
     * Get inbound batch by ID
     */
    public function showInboundBatch(int $id): JsonResponse
    {
        try {
            $batch = $this->warehouseService->getInboundBatch($id);

            return $this->successResponse($batch);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
        }
    }

    /**
     * BR-03.1: Tạo Quality Check (chỉ trên Batch)
     */
    public function createQualityCheck(QualityCheckRequest $request): JsonResponse
    {
        try {
            $qc = $this->warehouseService->createQualityCheck($request->validated());

            return $this->createdResponse($qc, 'Quality check created successfully');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return $this->validationErrorResponse($ex->errors());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
        }
    }

    /**
     * Get quality checks
     */
    public function qualityChecks(): JsonResponse
    {
        try {
            $checks = $this->warehouseService->getQualityChecks();

            return $this->successResponse($checks);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while processing the request', $ex);
        }
    }

    /**
     * BR-05.1, BR-05.2, BR-05.3: Điều chỉnh tồn kho
     */
    public function adjustStock(Warehouse $warehouse, StockAdjustmentRequest $request): JsonResponse
    {
        try {
            $stock = $this->warehouseService->adjustStock($warehouse->id, $request->validated());

            return $this->successResponse($stock, 'Stock adjusted successfully');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return $this->validationErrorResponse($ex->errors());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
        }
    }

    /**
     * BR-06.1, BR-06.2, BR-06.3: Xuất kho
     */
    public function outboundStock(Warehouse $warehouse, StockOutboundRequest $request): JsonResponse
    {
        try {
            $stock = $this->warehouseService->outboundStock($warehouse->id, $request->validated());

            return $this->successResponse($stock, 'Stock outbound successfully');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Illuminate\Validation\ValidationException $ex) {
            return $this->validationErrorResponse($ex->errors());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
        }
    }

    /**
     * Get inventory logs (BR-09.2)
     */
    public function inventoryLogs(Warehouse $warehouse, Request $request): JsonResponse
    {
        try {
            if (!$warehouse || !$warehouse->id) {
                return $this->notFoundResponse('Warehouse not found');
            }

            $perPage = $request->query('per_page', 20);
            $logs = $this->warehouseService->getInventoryLogs($warehouse->id, $perPage);

            return $this->successResponse($logs);
        } catch (NotFoundException $ex) {
            return $this->notFoundResponse($ex->getMessage());
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage() ?: 'An error occurred while processing the request', $ex);
        }
    }

    /**
     * Get warehouse dashboard stats
     */
    public function dashboardStats(): JsonResponse
    {
        try {
            $stats = $this->warehouseService->getDashboardStats();

            return $this->successResponse($stats);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('An error occurred while processing the request', $ex);
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

            return $this->successResponse($check);
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Delete quality check (BR-09.1: Không được xóa)
     */
    public function deleteQualityCheck(int $id): JsonResponse
    {
        try {
            $this->warehouseService->deleteQualityCheck($id);

            return $this->deletedResponse('Quality check deleted');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    // ==================== INBOUND RECEIPTS ====================

    /**
     * Get all inbound receipts
     */
    public function inboundReceipts(Request $request): JsonResponse
    {
        try {
            $receipts = $this->warehouseService->getInboundReceipts($request->only(['status', 'warehouse_id']));
            return $this->successResponse($receipts);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Create inbound receipt (Phiếu nhập)
     */
    public function createInboundReceipt(InboundReceiptRequest $request): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->createInboundReceipt($request->validated());

            return $this->createdResponse($receipt, 'Inbound receipt created');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Update inbound receipt
     */
    public function updateInboundReceipt(int $id, Request $request): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->updateInboundReceipt($id, $request->all());
            return $this->successResponse($receipt);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Approve inbound receipt
     */
    public function approveInboundReceipt(int $id): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->approveInboundReceipt($id);
            return $this->successResponse($receipt, 'Receipt approved');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Cancel inbound receipt
     */
    public function cancelInboundReceipt(int $id): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->cancelInboundReceipt($id);
            return $this->successResponse($receipt, 'Receipt cancelled');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Delete inbound receipt (only pending)
     */
    public function deleteInboundReceipt(int $id): JsonResponse
    {
        try {
            $this->warehouseService->deleteInboundReceipt($id);
            return $this->deletedResponse('Receipt deleted');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    // ==================== OUTBOUND RECEIPTS ====================

    /**
     * Get all outbound receipts
     */
    public function outboundReceipts(Request $request): JsonResponse
    {
        try {
            $receipts = $this->warehouseService->getOutboundReceipts($request->only(['status', 'purpose']));
            return $this->successResponse($receipts);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Create outbound receipt (Phiếu xuất)
     */
    public function createOutboundReceipt(OutboundReceiptRequest $request): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->createOutboundReceipt($request->validated());

            return $this->createdResponse($receipt, 'Outbound receipt created');
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Update outbound receipt
     */
    public function updateOutboundReceipt(int $id, Request $request): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->updateOutboundReceipt($id, $request->all());
            return $this->successResponse($receipt);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Approve outbound receipt (reserve stock)
     */
    public function approveOutboundReceipt(int $id): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->approveOutboundReceipt($id);
            return $this->successResponse($receipt, 'Receipt approved, stock reserved');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Complete outbound receipt (deduct stock)
     */
    public function completeOutboundReceipt(int $id): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->completeOutboundReceipt($id);
            return $this->successResponse($receipt, 'Outbound completed, stock deducted');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Cancel outbound receipt
     */
    public function cancelOutboundReceipt(int $id): JsonResponse
    {
        try {
            $receipt = $this->warehouseService->cancelOutboundReceipt($id);
            return $this->successResponse($receipt, 'Receipt cancelled');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    // ==================== STOCK ADJUSTMENTS ====================

    /**
     * Get all stock adjustments
     */
    public function stockAdjustments(Request $request): JsonResponse
    {
        try {
            $adjustments = $this->warehouseService->getStockAdjustments($request->only(['warehouse_id']));
            return $this->successResponse($adjustments);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }

    /**
     * Create stock adjustment (BR-05)
     */
    public function createStockAdjustment(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'stock_id' => 'required|exists:stocks,id',
                'previous_quantity' => 'required|integer|min:0',
                'new_quantity' => 'required|integer|min:0',
                'adjustment_quantity' => 'required|integer',
                'reason' => 'required|string|min:3', // BR-05.2: Bắt buộc có lý do
                'notes' => 'nullable|string',
            ]);

            $adjustment = $this->warehouseService->createStockAdjustment($request->all());

            return $this->createdResponse($adjustment, 'Stock adjusted successfully');
        } catch (BusinessLogicException $ex) {
            return $this->errorResponse($ex->getMessage(), null, 400);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse($ex->getMessage(), $ex);
        }
    }
}


