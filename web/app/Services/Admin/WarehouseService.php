<?php

namespace App\Services\Admin;

use App\Exceptions\NotFoundException;
use App\Exceptions\BusinessLogicException;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use App\Repositories\Contracts\InventoryLogRepositoryInterface;
use App\Models\InboundBatch;
use App\Models\InboundBatchItem;
use App\Models\QualityCheck;
use App\Models\Stock;
use App\Models\InventoryLog;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WarehouseService
{
    public function __construct(
        private WarehouseRepositoryInterface $warehouseRepository,
        private StockRepositoryInterface $stockRepository,
        private InventoryLogRepositoryInterface $inventoryLogRepository
    ) {
    }

    /**
     * Get all warehouses
     */
    public function getAll(): Collection
    {
        return $this->warehouseRepository->getAll();
    }

    /**
     * Get warehouse by ID
     */
    public function getById(int $id): array
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$id} not found");
        }

        return $warehouse->toArray();
    }

    /**
     * Create warehouse
     */
    public function create(array $data): array
    {
        $warehouse = $this->warehouseRepository->create($data);
        return $warehouse->toArray();
    }

    /**
     * Update warehouse
     */
    public function update(int $id, array $data): array
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$id} not found");
        }

        $warehouse = $this->warehouseRepository->update($warehouse, $data);
        return $warehouse->toArray();
    }

    /**
     * Delete warehouse
     */
    public function delete(int $id): void
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$id} not found");
        }

        $this->warehouseRepository->delete($warehouse);
    }

    /**
     * Get stocks by warehouse ID
     * BR-06.1: Chỉ hiển thị Available Inventory
     */
    public function getStocks(int $warehouseId): Collection
    {
        $warehouse = $this->warehouseRepository->findById($warehouseId);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$warehouseId} not found");
        }

        return $this->stockRepository->getByWarehouse($warehouseId);
    }

    /**
     * BR-02.1: Tạo Inbound Batch
     * Mỗi lần nhập phải tạo Inbound Batch
     */
    public function createInboundBatch(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['batch_number'] = $this->generateBatchNumber();
        $data['status'] = InboundBatch::STATUS_PENDING;

        $batch = InboundBatch::create($data);

        // Tạo batch items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                InboundBatchItem::create([
                    'inbound_batch_id' => $batch->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity_received' => $item['quantity_received'] ?? 0,
                ]);
            }
        }

        return $batch->load(['warehouse', 'supplier', 'items.product', 'items.productVariant'])->toArray();
    }

    /**
     * BR-02.2: Nhận hàng (RECEIVED)
     * Batch chỉ ghi nhận số lượng nhận, không cộng tồn
     */
    public function receiveInboundBatch(int $batchId, array $data): array
    {
        $batch = InboundBatch::findOrFail($batchId);

        // BR-02.3: Batch không được sửa sau khi QC bắt đầu
        if (!$batch->canBeEdited()) {
            throw new BusinessLogicException('Batch cannot be edited after QC has started');
        }

        $batch->update([
            'status' => InboundBatch::STATUS_RECEIVED,
            'received_date' => $data['received_date'] ?? Carbon::now(),
            'notes' => $data['notes'] ?? null,
        ]);

        // Cập nhật quantity_received cho từng item nếu có
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $item = InboundBatchItem::where('inbound_batch_id', $batch->id)
                    ->where('product_id', $itemData['product_id'])
                    ->where('product_variant_id', $itemData['product_variant_id'] ?? null)
                    ->first();

                if ($item) {
                    $item->update(['quantity_received' => $itemData['quantity_received']]);
                }
            }
        }

        // BR-02.2: Batch chỉ ghi nhận số lượng nhận, không cộng tồn
        // BR-09.2: Mọi biến động tồn kho đều phải có log
        // Nhưng khi RECEIVED, inventory chưa thay đổi, nên không cần log ở đây
        // Log sẽ được tạo khi QC PASS và tạo inventory (BR-04.1)
        // Nếu muốn audit trail, có thể log cho từng item nhưng không ảnh hưởng inventory

        return $batch->load(['warehouse', 'supplier', 'items.product', 'items.productVariant'])->toArray();
    }

    /**
     * BR-03.1: QC bắt buộc theo Batch
     * BR-03.2: Một Batch chỉ có 1 kết quả QC chính thức
     * BR-03.3: QC phải phân loại rõ kết quả (PASS, FAIL, PARTIAL)
     */
    public function createQualityCheck(array $data): array
    {
        $batch = InboundBatch::findOrFail($data['inbound_batch_id']);

        // Batch phải ở trạng thái RECEIVED
        if ($batch->status !== InboundBatch::STATUS_RECEIVED) {
            throw new BusinessLogicException('Batch must be in RECEIVED status before QC');
        }

        // BR-02.3: Batch không được sửa sau khi QC bắt đầu
        if (!$batch->canBeEdited()) {
            throw new BusinessLogicException('Batch cannot be edited after QC has started');
        }

        // BR-03.2: Kiểm tra xem đã có QC chính thức chưa
        if (QualityCheck::hasOfficialQC($batch->id)) {
            throw new BusinessLogicException('Batch already has an official quality check');
        }

        // Cập nhật status batch
        $batch->update(['status' => InboundBatch::STATUS_QC_IN_PROGRESS]);

        $qc = QualityCheck::create([
            'inbound_batch_id' => $batch->id,
            'warehouse_id' => $batch->warehouse_id,
            'product_id' => $data['product_id'],
            'supplier_id' => $batch->supplier_id,
            'inspector_id' => Auth::id(),
            'check_date' => $data['check_date'] ?? Carbon::now(),
            'status' => $data['status'], // pass, fail, partial
            'score' => $data['score'] ?? 0,
            'quantity_passed' => $data['quantity_passed'] ?? 0,
            'quantity_failed' => $data['quantity_failed'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'issues' => $data['issues'] ?? null,
        ]);

        // BR-03.4 & BR-04.1: Nếu QC PASS hoặc PARTIAL, tự động tạo Inventory
        if ($qc->status === QualityCheck::STATUS_PASS || $qc->status === QualityCheck::STATUS_PARTIAL) {
            $this->createInventoryFromQC($qc, $batch);
        }

        // Cập nhật status batch
        $batch->update(['status' => InboundBatch::STATUS_QC_COMPLETED]);

        return $qc->load(['inboundBatch', 'warehouse', 'product', 'supplier', 'inspector'])->toArray();
    }

    /**
     * BR-04.1: Inventory chỉ được tạo khi QC = PASS
     * BR-04.2: Inventory phải gắn nguồn Batch & QC
     */
    private function createInventoryFromQC(QualityCheck $qc, InboundBatch $batch): void
    {
        DB::transaction(function () use ($qc, $batch) {
            foreach ($batch->items as $item) {
                $quantityToAdd = $qc->status === QualityCheck::STATUS_PASS
                    ? $item->quantity_received
                    : $qc->quantity_passed;

                if ($quantityToAdd <= 0) {
                    continue;
                }

                // Tìm stock hiện có (không có batch_id và qc_id để có thể gộp nhiều batch)
                // Hoặc tạo mới với batch_id và qc_id để truy vết
                $stock = $this->stockRepository->firstOrCreate(
                    [
                        'warehouse_id' => $batch->warehouse_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                    ],
                    [
                        'quantity' => 0,
                        'available_quantity' => 0,
                        'inbound_batch_id' => $batch->id,
                        'quality_check_id' => $qc->id,
                    ]
                );

                // Nếu stock đã tồn tại nhưng chưa có batch_id, cập nhật để truy vết
                if (!$stock->inbound_batch_id) {
                    $stock->inbound_batch_id = $batch->id;
                    $stock->quality_check_id = $qc->id;
                }

                $oldQuantity = $stock->quantity;
                $oldAvailableQuantity = $stock->available_quantity;

                // Cập nhật tồn kho
                $stock = $this->stockRepository->update($stock, [
                    'quantity' => $oldQuantity + $quantityToAdd,
                    'available_quantity' => $oldAvailableQuantity + $quantityToAdd,
                ]);

                // Ghi log - BR-09.2
                $this->createInventoryLog([
                    'warehouse_id' => $batch->warehouse_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'movement_type' => InventoryLog::MOVEMENT_TYPE_QC_PASS,
                    'quantity' => $quantityToAdd,
                    'quantity_before' => $oldQuantity,
                    'quantity_after' => $stock->quantity,
                    'inbound_batch_id' => $batch->id,
                    'quality_check_id' => $qc->id,
                    'user_id' => Auth::id(),
                    'note' => "Inventory created from QC PASS - Batch {$batch->batch_number}",
                ]);
            }
        });
    }

    /**
     * BR-05.1: Điều chỉnh tồn kho chỉ role quản lý
     * BR-05.2: Bắt buộc có lý do
     * BR-05.3: Mọi điều chỉnh phải ghi Inventory Log
     */
    public function adjustStock(int $warehouseId, array $data): array
    {
        // BR-05.2: Bắt buộc có lý do
        if (empty($data['reason'])) {
            throw new BusinessLogicException('Reason is required for stock adjustment');
        }

        // BR-05.1: Kiểm tra phân quyền (cần implement middleware hoặc check role)
        // TODO: Implement role check

        $stock = $this->stockRepository->findByWarehouseProduct(
            $warehouseId,
            $data['product_id'],
            $data['product_variant_id'] ?? null
        );

        if (!$stock) {
            throw new NotFoundException('Stock not found');
        }

        $oldQuantity = $stock->quantity;
        $oldAvailableQuantity = $stock->available_quantity;
        $newQuantity = max(0, abs($data['quantity']));
        $newAvailableQuantity = max(0, abs($data['available_quantity'] ?? $newQuantity));

        // BR-04.3: Inventory không được sửa trực tiếp, nhưng điều chỉnh là nghiệp vụ đặc biệt
        $stock = $this->stockRepository->update($stock, [
            'quantity' => $newQuantity,
            'available_quantity' => $newAvailableQuantity,
        ]);

        // BR-05.3: Ghi log
        $this->createInventoryLog([
            'warehouse_id' => $warehouseId,
            'product_id' => $data['product_id'],
            'product_variant_id' => $data['product_variant_id'] ?? null,
            'movement_type' => InventoryLog::MOVEMENT_TYPE_ADJUST,
            'quantity' => abs($newQuantity - $oldQuantity),
            'quantity_before' => $oldQuantity,
            'quantity_after' => $newQuantity,
            'user_id' => Auth::id(),
            'reason' => $data['reason'],
            'note' => $data['note'] ?? "Stock adjusted from {$oldQuantity} to {$newQuantity}",
        ]);

        return $stock->load(['product', 'productVariant', 'warehouse'])->toArray();
    }

    /**
     * BR-06.1: Chỉ xuất từ Available Inventory
     * BR-06.2: Không cho xuất vượt tồn
     * BR-06.3: Xuất kho theo FIFO/LIFO/FEFO
     */
    public function outboundStock(int $warehouseId, array $data): array
    {
        $stock = $this->stockRepository->findByWarehouseProduct(
            $warehouseId,
            $data['product_id'],
            $data['product_variant_id'] ?? null
        );

        if (!$stock) {
            throw new NotFoundException('Stock not found');
        }

        $quantityToOut = abs($data['quantity']);

        // BR-06.2: Không cho xuất vượt tồn
        if ($quantityToOut > $stock->available_quantity) {
            throw new BusinessLogicException("Cannot outbound more than available quantity. Available: {$stock->available_quantity}, Requested: {$quantityToOut}");
        }

        // BR-06.3: Xuất theo FIFO (có thể config sau)
        $oldQuantity = $stock->quantity;
        $oldAvailableQuantity = $stock->available_quantity;

        $stock = $this->stockRepository->update($stock, [
            'quantity' => max(0, $oldQuantity - $quantityToOut),
            'available_quantity' => max(0, $oldAvailableQuantity - $quantityToOut),
        ]);

        // BR-09.2: Ghi log
        $this->createInventoryLog([
            'warehouse_id' => $warehouseId,
            'product_id' => $data['product_id'],
            'product_variant_id' => $data['product_variant_id'] ?? null,
            'movement_type' => InventoryLog::MOVEMENT_TYPE_OUTBOUND,
            'quantity' => $quantityToOut,
            'quantity_before' => $oldQuantity,
            'quantity_after' => $stock->quantity,
            'user_id' => Auth::id(),
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'note' => $data['note'] ?? "Outbound {$quantityToOut} units",
        ]);

        return $stock->load(['product', 'productVariant', 'warehouse'])->toArray();
    }

    /**
     * Get inventory logs by warehouse
     */
    public function getInventoryLogs(int $warehouseId, int $perPage = 20): LengthAwarePaginator
    {
        $warehouse = $this->warehouseRepository->findById($warehouseId);

        if (!$warehouse) {
            throw new NotFoundException("Warehouse with ID {$warehouseId} not found");
        }

        return $this->inventoryLogRepository->getByWarehouse($warehouseId, $perPage);
    }

    /**
     * Get dashboard stats for warehouse
     */
    public function getDashboardStats(): array
    {
        return [
            'totalProducts' => Product::count(),
            'totalSuppliers' => Supplier::count(),
            'pendingBatches' => InboundBatch::where('status', InboundBatch::STATUS_PENDING)->count(),
            'receivedBatches' => InboundBatch::where('status', InboundBatch::STATUS_RECEIVED)->count(),
            'qcInProgress' => InboundBatch::where('status', InboundBatch::STATUS_QC_IN_PROGRESS)->count(),
            'lowStock' => Stock::where('quantity', '<=', 0)->count(),
        ];
    }

    /**
     * Get quality checks
     */
    public function getQualityChecks(): Collection
    {
        return QualityCheck::with(['inboundBatch', 'warehouse', 'product', 'supplier', 'inspector'])->latest()->get();
    }

    /**
     * Store a new quality check (legacy - use createQualityCheck instead)
     */
    public function storeQualityCheck(array $data): array
    {
        return $this->createQualityCheck($data);
    }

    /**
     * Update an existing quality check
     */
    public function updateQualityCheck(int $id, array $data): QualityCheck
    {
        $check = QualityCheck::findOrFail($id);

        // BR-03.2: Không cho sửa QC chính thức
        if (!$check->is_rollback) {
            throw new BusinessLogicException('Cannot update official quality check. Use rollback process instead.');
        }

        $check->update($data);
        return $check->fresh();
    }

    /**
     * Delete a quality check (BR-09.1: Không được xóa log)
     */
    public function deleteQualityCheck(int $id): bool
    {
        throw new BusinessLogicException('Quality checks cannot be deleted. Use rollback process instead.');
    }

    /**
     * Get inbound batches
     */
    public function getInboundBatches(array $filters = []): Collection
    {
        $query = InboundBatch::with(['warehouse', 'supplier', 'items.product', 'items.productVariant', 'qualityCheck']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Get inbound batch by ID
     */
    public function getInboundBatch(int $id): array
    {
        $batch = InboundBatch::with(['warehouse', 'supplier', 'items.product', 'items.productVariant', 'qualityCheck'])
            ->findOrFail($id);

        return $batch->toArray();
    }

    /**
     * Helper: Create inventory log
     */
    private function createInventoryLog(array $data): InventoryLog
    {
        return $this->inventoryLogRepository->create($data);
    }

    /**
     * Helper: Generate batch number
     */
    private function generateBatchNumber(): string
    {
        return 'BATCH-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Helper: Generate receipt number
     */
    private function generateReceiptNumber(string $prefix): string
    {
        return $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    // ==================== INBOUND RECEIPTS ====================

    /**
     * Get inbound receipts (Phiếu nhập)
     */
    public function getInboundReceipts(array $filters = []): Collection
    {
        // For now, use InboundBatch as receipts since the flow is similar
        // In production, may want separate InboundReceipt model
        $query = InboundBatch::with(['warehouse', 'supplier', 'items.product']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Create inbound receipt (Phiếu nhập)
     */
    public function createInboundReceipt(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            $data['batch_number'] = $this->generateReceiptNumber('IN-');
            $data['status'] = 'pending'; // Using 'pending' as it's valid in DB enum

            $receipt = InboundBatch::create([
                'batch_number' => $data['batch_number'],
                'warehouse_id' => $data['warehouse_id'],
                'supplier_id' => $data['supplier_id'],
                'created_by' => $data['created_by'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    InboundBatchItem::create([
                        'inbound_batch_id' => $receipt->id,
                        'product_id' => $item['product_id'],
                        'quantity_received' => $item['expected_qty'] ?? 0,
                    ]);
                }
            }

            return $receipt->load(['warehouse', 'supplier', 'items.product'])->toArray();
        });
    }

    /**
     * Update inbound receipt
     */
    public function updateInboundReceipt(int $id, array $data): array
    {
        $receipt = InboundBatch::findOrFail($id);

        if ($receipt->status !== 'pending') {
            throw new BusinessLogicException('Only pending receipts can be updated');
        }

        $receipt->update([
            'supplier_id' => $data['supplier_id'] ?? $receipt->supplier_id,
            'warehouse_id' => $data['warehouse_id'] ?? $receipt->warehouse_id,
            'notes' => $data['notes'] ?? $receipt->notes,
        ]);

        return $receipt->fresh(['warehouse', 'supplier', 'items.product'])->toArray();
    }

    /**
     * Approve inbound receipt
     */
    public function approveInboundReceipt(int $id): array
    {
        $receipt = InboundBatch::findOrFail($id);

        if ($receipt->status !== 'pending') {
            throw new BusinessLogicException('Only pending receipts can be approved');
        }

        $receipt->update(['status' => 'received']);

        return $receipt->fresh(['warehouse', 'supplier', 'items.product'])->toArray();
    }

    /**
     * Cancel inbound receipt
     */
    public function cancelInboundReceipt(int $id): array
    {
        $receipt = InboundBatch::findOrFail($id);

        if (!in_array($receipt->status, ['pending', 'received'])) {
            throw new BusinessLogicException('Cannot cancel receipt in current status');
        }

        $receipt->update(['status' => 'cancelled']);

        return $receipt->fresh(['warehouse', 'supplier', 'items.product'])->toArray();
    }

    /**
     * Delete inbound receipt (only pending)
     */
    public function deleteInboundReceipt(int $id): void
    {
        $receipt = InboundBatch::findOrFail($id);

        if ($receipt->status !== 'pending') {
            throw new BusinessLogicException('Only pending receipts can be deleted');
        }

        // Delete related items first
        $receipt->items()->delete();
        $receipt->delete();
    }

    // ==================== OUTBOUND RECEIPTS ====================

    /**
     * Get outbound receipts (Phiếu xuất)
     * Note: Using InventoryLog as proxy for outbound receipts for now
     * Transform to proper receipt format for frontend
     */
    public function getOutboundReceipts(array $filters = []): SupportCollection
    {
        $query = InventoryLog::with(['warehouse', 'product', 'user'])
            ->where('movement_type', InventoryLog::MOVEMENT_TYPE_OUTBOUND);

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['purpose'])) {
            $query->where('note', 'like', "%Purpose: {$filters['purpose']}%");
        }

        $logs = $query->latest()->get();

        // Transform logs to receipt format for frontend
        return $logs->map(function ($log) {
            // Extract purpose from note if exists
            $purpose = 'sales';
            if (preg_match('/Purpose:\s*(\w+)/', $log->note ?? '', $matches)) {
                $purpose = $matches[1];
            }

            // Extract receipt number from note
            $receiptNumber = 'OUT-' . date('Ymd', strtotime($log->created_at)) . '-' . strtoupper(substr(md5($log->id), 0, 6));
            if (preg_match('/Outbound receipt\s*(OUT-[\w-]+)/', $log->note ?? '', $matches)) {
                $receiptNumber = $matches[1];
            }

            return [
                'id' => $log->id,
                'receipt_number' => $receiptNumber,
                'warehouse_id' => $log->warehouse_id,
                'warehouse' => $log->warehouse,
                'purpose' => $purpose,
                'status' => 'completed', // InventoryLog = already completed
                'items' => [[
                    'product_id' => $log->product_id,
                    'product' => $log->product,
                    'quantity' => abs($log->quantity),
                ]],
                'notes' => $log->note,
                'created_by' => $log->user_id,
                'user' => $log->user,
                'created_at' => $log->created_at->format('d/m/Y H:i'),
                'updated_at' => $log->updated_at?->format('d/m/Y H:i'),
            ];
        });
    }

    /**
     * Create outbound receipt (Phiếu xuất)
     */
    public function createOutboundReceipt(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $receiptNumber = $this->generateReceiptNumber('OUT-');
            $results = [];

            foreach ($data['items'] as $item) {
                $stock = Stock::findOrFail($item['stock_id']);

                if ($stock->available_quantity < $item['quantity']) {
                    throw new BusinessLogicException(
                        "Insufficient stock for product {$stock->product_id}. Available: {$stock->available_quantity}"
                    );
                }

                // Record outbound (create log entry)
                $log = $this->createInventoryLog([
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $stock->product_id,
                    'product_variant_id' => $stock->product_variant_id,
                    'movement_type' => InventoryLog::MOVEMENT_TYPE_OUTBOUND,
                    'quantity' => $item['quantity'],
                    'quantity_before' => $stock->quantity,
                    'quantity_after' => $stock->quantity - $item['quantity'],
                    'user_id' => Auth::id(),
                    'note' => "Outbound receipt {$receiptNumber} - Purpose: {$data['purpose']}",
                ]);

                $results[] = $log;
            }

            return [
                'receipt_number' => $receiptNumber,
                'status' => 'pending',
                'items' => $results,
            ];
        });
    }

    /**
     * Update outbound receipt
     */
    public function updateOutboundReceipt(int $id, array $data): array
    {
        throw new BusinessLogicException('Outbound receipts cannot be updated');
    }

    /**
     * Approve outbound receipt (reserve stock)
     */
    public function approveOutboundReceipt(int $id): array
    {
        // For now, this is a placeholder
        // Would implement stock reservation logic
        return ['status' => 'approved', 'id' => $id];
    }

    /**
     * Complete outbound receipt (deduct stock)
     */
    public function completeOutboundReceipt(int $id): array
    {
        // For now, this is a placeholder
        // Would implement final stock deduction
        return ['status' => 'completed', 'id' => $id];
    }

    /**
     * Cancel outbound receipt
     */
    public function cancelOutboundReceipt(int $id): array
    {
        // For now, this is a placeholder
        // Would implement stock release logic
        return ['status' => 'cancelled', 'id' => $id];
    }

    // ==================== STOCK ADJUSTMENTS ====================

    /**
     * Get stock adjustments
     */
    public function getStockAdjustments(array $filters = []): Collection
    {
        $query = InventoryLog::with(['warehouse', 'product', 'user'])
            ->where('movement_type', InventoryLog::MOVEMENT_TYPE_ADJUST);

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Create stock adjustment (BR-05)
     */
    public function createStockAdjustment(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $stock = Stock::findOrFail($data['stock_id']);

            // Update stock quantity
            $stock->update([
                'quantity' => $data['new_quantity'],
                'available_quantity' => max(0, $data['new_quantity'] - ($stock->quantity - $stock->available_quantity)),
            ]);

            // Create adjustment log
            $log = $this->createInventoryLog([
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $stock->product_id,
                'product_variant_id' => $stock->product_variant_id,
                'movement_type' => InventoryLog::MOVEMENT_TYPE_ADJUST,
                'quantity' => abs($data['adjustment_quantity']),
                'quantity_before' => $data['previous_quantity'],
                'quantity_after' => $data['new_quantity'],
                'user_id' => Auth::id(),
                'reason' => $data['reason'],
                'note' => $data['notes'] ?? "Stock adjusted from {$data['previous_quantity']} to {$data['new_quantity']}",
            ]);

            return $log->toArray();
        });
    }
}

