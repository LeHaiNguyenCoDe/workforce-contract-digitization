<?php

namespace App\Services\Admin;

use App\Models\InventorySetting;
use App\Models\PurchaseRequest;
use App\Models\Stock;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryAlertService
{
    /**
     * Get all inventory settings
     */
    public function getAllSettings(array $filters = [], int $perPage = 15)
    {
        $query = InventorySetting::with(['product:id,name,sku', 'warehouse:id,name']);

        if (!empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get or create setting for product/warehouse
     */
    public function getSetting(int $productId, ?int $warehouseId = null): ?InventorySetting
    {
        return InventorySetting::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();
    }

    /**
     * Create or update inventory setting
     */
    public function saveSetting(array $data): InventorySetting
    {
        return InventorySetting::updateOrCreate(
            [
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'] ?? null,
            ],
            [
                'min_quantity' => $data['min_quantity'] ?? 0,
                'max_quantity' => $data['max_quantity'] ?? 0,
                'reorder_quantity' => $data['reorder_quantity'] ?? 0,
                'auto_create_purchase_request' => $data['auto_create_purchase_request'] ?? false,
            ]
        );
    }

    /**
     * Delete inventory setting
     */
    public function deleteSetting(int $id): bool
    {
        return InventorySetting::findOrFail($id)->delete();
    }

    /**
     * Get all alerts (products below min or above max)
     */
    public function getAlerts(?int $warehouseId = null): array
    {
        $settings = InventorySetting::with(['product:id,name,sku,thumbnail', 'warehouse:id,name'])
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        $alerts = [
            'low_stock' => [],
            'over_stock' => [],
            'expiring_soon' => [],
        ];

        foreach ($settings as $setting) {
            // Get current stock
            $currentStock = $this->getCurrentStock($setting->product_id, $setting->warehouse_id);
            
            if ($setting->isBelowMin($currentStock)) {
                $alerts['low_stock'][] = [
                    'setting_id' => $setting->id,
                    'product' => $setting->product,
                    'warehouse' => $setting->warehouse,
                    'current_stock' => $currentStock,
                    'min_quantity' => $setting->min_quantity,
                    'shortage' => $setting->min_quantity - $currentStock,
                    'recommended_order' => $setting->getRecommendedOrderQuantity($currentStock),
                    'auto_create' => $setting->auto_create_purchase_request,
                ];
            }

            if ($setting->isAboveMax($currentStock)) {
                $alerts['over_stock'][] = [
                    'setting_id' => $setting->id,
                    'product' => $setting->product,
                    'warehouse' => $setting->warehouse,
                    'current_stock' => $currentStock,
                    'max_quantity' => $setting->max_quantity,
                    'excess' => $currentStock - $setting->max_quantity,
                ];
            }
        }

        // Get expiring soon (within 30 days)
        $alerts['expiring_soon'] = $this->getExpiringSoon(30, $warehouseId);

        return $alerts;
    }

    /**
     * Get summary counts
     */
    public function getAlertSummary(?int $warehouseId = null): array
    {
        $alerts = $this->getAlerts($warehouseId);
        
        return [
            'low_stock_count' => count($alerts['low_stock']),
            'over_stock_count' => count($alerts['over_stock']),
            'expiring_soon_count' => count($alerts['expiring_soon']),
            'total_alerts' => count($alerts['low_stock']) + count($alerts['over_stock']) + count($alerts['expiring_soon']),
        ];
    }

    /**
     * Get current stock for product
     */
    public function getCurrentStock(int $productId, ?int $warehouseId = null): int
    {
        // First try from Stock table
        $stockQuery = Stock::where('product_id', $productId);
        if ($warehouseId) {
            $stockQuery->where('warehouse_id', $warehouseId);
        }
        $stockTotal = $stockQuery->sum('quantity');
        
        if ($stockTotal > 0) {
            return $stockTotal;
        }

        // Fallback to Batch remaining_quantity
        $batchQuery = Batch::where('product_id', $productId)
            ->where('status', 'available');
        if ($warehouseId) {
            $batchQuery->where('warehouse_id', $warehouseId);
        }
        
        return $batchQuery->sum('remaining_quantity');
    }

    /**
     * Get expiring soon batches
     */
    public function getExpiringSoon(int $days = 30, ?int $warehouseId = null): array
    {
        return Batch::with(['product:id,name,sku', 'warehouse:id,name'])
            ->where('status', 'available')
            ->where('remaining_quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now())
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($batch) {
                return [
                    'batch_id' => $batch->id,
                    'batch_code' => $batch->batch_code,
                    'product' => $batch->product,
                    'warehouse' => $batch->warehouse,
                    'remaining_quantity' => $batch->remaining_quantity,
                    'expiry_date' => $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : null,
                    'days_until_expiry' => $batch->expiry_date ? now()->diffInDays($batch->expiry_date, false) : null,
                ];
            })
            ->toArray();
    }

    /**
     * Check stock levels and auto-create purchase requests
     */
    public function checkAndCreatePurchaseRequests(): array
    {
        $created = [];

        $settings = InventorySetting::where('auto_create_purchase_request', true)
            ->with('product:id,name')
            ->get();

        foreach ($settings as $setting) {
            $currentStock = $this->getCurrentStock($setting->product_id, $setting->warehouse_id);
            
            if (!$setting->isBelowMin($currentStock)) {
                continue;
            }

            // Check if there's already a pending request
            $existingRequest = PurchaseRequest::where('product_id', $setting->product_id)
                ->where('warehouse_id', $setting->warehouse_id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingRequest) {
                continue; // Skip if already has pending request
            }

            // Create purchase request
            $request = PurchaseRequest::create([
                'request_code' => PurchaseRequest::generateRequestCode(),
                'product_id' => $setting->product_id,
                'warehouse_id' => $setting->warehouse_id,
                'supplier_id' => null, // Can be set later
                'requested_quantity' => $setting->getRecommendedOrderQuantity($currentStock),
                'current_stock' => $currentStock,
                'min_stock' => $setting->min_quantity,
                'status' => PurchaseRequest::STATUS_PENDING,
                'source' => PurchaseRequest::SOURCE_AUTO,
                'notes' => 'Tự động tạo do tồn kho dưới ngưỡng tối thiểu',
            ]);

            $created[] = $request;

            Log::info('Auto-created purchase request', [
                'request_id' => $request->id,
                'product_id' => $setting->product_id,
                'current_stock' => $currentStock,
                'min_stock' => $setting->min_quantity,
            ]);
        }

        return $created;
    }
}
