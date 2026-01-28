<?php

use App\Http\Controllers\Modules\Admin\ArticleController;
use App\Http\Controllers\Modules\Admin\BatchController;
use App\Http\Controllers\Modules\Admin\CategoryController;
use App\Http\Controllers\Modules\Admin\DebtController;
use App\Http\Controllers\Modules\Admin\ExpenseController;
use App\Http\Controllers\Modules\Admin\FinanceController;
use App\Http\Controllers\Modules\Admin\InventoryAlertController;
use App\Http\Controllers\Modules\Admin\MembershipController;
use App\Http\Controllers\Modules\Admin\OrderController;
use App\Http\Controllers\Modules\Admin\ProductController;
use App\Http\Controllers\Modules\Admin\PromotionController;
use App\Http\Controllers\Modules\Admin\PurchaseRequestController;
use App\Http\Controllers\Modules\Admin\ReportController;
use App\Http\Controllers\Modules\Admin\ReturnController;
use App\Http\Controllers\Modules\Admin\ReviewController;
use App\Http\Controllers\Modules\Admin\StocktakeController;
use App\Http\Controllers\Modules\Admin\SupplierController;
use App\Http\Controllers\Modules\Admin\TransferController;
use App\Http\Controllers\Modules\Admin\UserController;
use App\Http\Controllers\Modules\Admin\WarehouseController;
use App\Http\Controllers\Modules\Admin\TranslationController;
use App\Http\Controllers\Modules\Admin\SettingsController;
use App\Http\Controllers\Modules\Admin\QuotationController;
use App\Http\Controllers\Modules\Admin\ShippingController;
use App\Http\Controllers\Modules\Admin\EmployeeController;
use App\Http\Controllers\Modules\Admin\TaskController;
use App\Http\Controllers\Modules\Admin\AppointmentController;
use App\Http\Controllers\Modules\Admin\WarrantyController;
use App\Http\Controllers\Modules\Admin\ApiLogController;
use App\Http\Controllers\Modules\Admin\ImportExportController;
use App\Http\Controllers\Modules\Admin\EmailController;
use App\Http\Controllers\Modules\Landing\CodReconciliationController;
use App\Http\Controllers\Modules\Admin\AutomationController;
use App\Http\Controllers\Modules\Admin\PointController;
use App\Http\Controllers\Modules\Admin\AuditLogController;
use App\Http\Controllers\Modules\Admin\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Modules Configuration
|--------------------------------------------------------------------------
| Define modules as an array to facilitate automatic route registration.
| format: 'uri' => ['controller' => Controller::class, 'resource' => bool, 'custom' => callback]
*/

$modules = [
    'users' => [
        'controller' => UserController::class,
        'resource' => true,
        'custom' => function() {
            Route::get('{user}/orders', [UserController::class, 'orders']);
        }
    ],
    'products' => [
        'controller' => ProductController::class,
        'resource' => true,
        'custom' => function() {
            Route::post('{product}/images', [ProductController::class, 'addImage']);
            Route::delete('{product}/images/{image}', [ProductController::class, 'removeImage']);
            Route::post('{product}/variants', [ProductController::class, 'addVariant']);
            Route::put('{product}/variants/{variant}', [ProductController::class, 'updateVariant']);
            Route::delete('{product}/variants/{variant}', [ProductController::class, 'removeVariant']);
        }
    ],
    'categories' => ['controller' => CategoryController::class, 'resource' => true],
    'customers' => [
        'controller' => UserController::class, // Re-use UserController for customer logic if needed, or create CustomerController
        'custom' => function() {
            Route::get('{id}/points', [PointController::class, 'show']);
            Route::get('{id}/points/transactions', [PointController::class, 'transactions']);
            Route::post('{id}/points/redeem', [PointController::class, 'redeem']);
            Route::post('{id}/points/adjust', [PointController::class, 'adjust']);
        }
    ],
    'orders' => [
        'controller' => OrderController::class,
        'custom' => function() {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('{order}', [OrderController::class, 'show']);
            Route::put('{order}/status', [OrderController::class, 'updateStatus']);
            Route::put('{order}/cancel', [OrderController::class, 'cancelOrder']);
            Route::get('{order}/check-stock', [OrderController::class, 'checkStock']);
            Route::post('{order}/assign-shipper', [OrderController::class, 'assignShipper']);
            Route::put('{order}/tracking', [OrderController::class, 'updateTracking']);
            Route::post('{order}/confirm', [OrderController::class, 'confirmOrder']);
            Route::post('{order}/deliver', [OrderController::class, 'markDelivered']);
            Route::post('{order}/complete', [OrderController::class, 'completeOrder']);
        }
    ],
    'finance' => [
        'controller' => FinanceController::class,
        'custom' => function() {
            Route::get('funds', [FinanceController::class, 'getFunds']);
            Route::get('transactions', [FinanceController::class, 'getTransactions']);
            Route::post('transactions', [FinanceController::class, 'createTransaction']);
            Route::get('summary', [FinanceController::class, 'getSummary']);
        }
    ],
    'debts' => [
        'controller' => DebtController::class,
        'custom' => function() {
            Route::get('receivables', [DebtController::class, 'getReceivables']);
            Route::get('receivables/summary', [DebtController::class, 'getReceivableSummary']);
            Route::post('receivables/{receivable}/pay', [DebtController::class, 'payReceivable']);
            Route::get('payables', [DebtController::class, 'getPayables']);
            Route::get('payables/summary', [DebtController::class, 'getPayableSummary']);
            Route::post('payables/{payable}/pay', [DebtController::class, 'payPayable']);
        }
    ],
    'promotions' => [
        'controller' => PromotionController::class,
        'resource' => true,
        'custom' => function() {
            Route::post('{promotion}/items', [PromotionController::class, 'addItem']);
            Route::delete('{promotion}/items/{item}', [PromotionController::class, 'removeItem']);
        }
    ],
    'warehouses' => [
        'controller' => WarehouseController::class,
        'resource' => true,
        'custom' => function() {
            Route::get('dashboard-stats', [WarehouseController::class, 'dashboardStats']);
            
            // Sub-modules within warehouses
            $sub = ['inbound-receipts', 'inbound-batches', 'quality-checks', 'outbound-receipts'];
            foreach($sub as $s) {
                Route::prefix($s)->group(function() use ($s) {
                    $method = \Str::camel($s);
                    Route::get('/', [WarehouseController::class, $method]);
                    if($s === 'inbound-receipts') {
                        Route::post('/', [WarehouseController::class, 'createInboundReceipt']);
                        Route::put('{id}', [WarehouseController::class, 'updateInboundReceipt']);
                        Route::post('{id}/approve', [WarehouseController::class, 'approveInboundReceipt']);
                        Route::post('{id}/cancel', [WarehouseController::class, 'cancelInboundReceipt']);
                        Route::delete('{id}', [WarehouseController::class, 'deleteInboundReceipt']);
                    }
                    if($s === 'inbound-batches') {
                        Route::post('/', [WarehouseController::class, 'createInboundBatch']);
                        Route::get('{id}', [WarehouseController::class, 'showInboundBatch']);
                        Route::post('{id}/receive', [WarehouseController::class, 'receiveInboundBatch']);
                    }
                    if($s === 'quality-checks') {
                        Route::post('/', [WarehouseController::class, 'createQualityCheck']);
                        Route::put('{id}', [WarehouseController::class, 'updateQualityCheck']);
                        Route::delete('{id}', [WarehouseController::class, 'deleteQualityCheck']);
                    }
                    if($s === 'outbound-receipts') {
                        Route::post('/', [WarehouseController::class, 'createOutboundReceipt']);
                        Route::put('{id}', [WarehouseController::class, 'updateOutboundReceipt']);
                        Route::post('{id}/approve', [WarehouseController::class, 'approveOutboundReceipt']);
                        Route::post('{id}/complete', [WarehouseController::class, 'completeOutboundReceipt']);
                        Route::post('{id}/cancel', [WarehouseController::class, 'cancelOutboundReceipt']);
                    }
                });
            }

            Route::get('stock-adjustments', [WarehouseController::class, 'stockAdjustments']);
            Route::post('stock-adjustments', [WarehouseController::class, 'createStockAdjustment']);
            Route::get('{warehouse}/stocks', [WarehouseController::class, 'stocks']);
            Route::post('{warehouse}/stocks/adjust', [WarehouseController::class, 'adjustStock']);
            Route::post('{warehouse}/stocks/outbound', [WarehouseController::class, 'outboundStock']);
            Route::get('{warehouse}/inventory-logs', [WarehouseController::class, 'inventoryLogs']);
        }
    ],
    'batches' => [
        'controller' => BatchController::class,
        'custom' => function() {
            Route::get('expiring-soon', [BatchController::class, 'expiringSoon']);
            Route::get('product/{productId}', [BatchController::class, 'getProductBatches']);
            Route::apiResource('/', BatchController::class)->parameters(['' => 'id']);
        }
    ],
    'stocktakes' => [
        'controller' => StocktakeController::class,
        'custom' => function() {
            Route::apiResource('/', StocktakeController::class)->only(['index', 'store', 'show', 'destroy'])->parameters(['' => 'id']);
            Route::post('{id}/start', [StocktakeController::class, 'start']);
            Route::put('{id}/items', [StocktakeController::class, 'updateItems']);
            Route::post('{id}/complete', [StocktakeController::class, 'complete']);
            Route::post('{id}/approve', [StocktakeController::class, 'approve']);
            Route::post('{id}/cancel', [StocktakeController::class, 'cancel']);
        }
    ],
    'inventory' => [
        'controller' => InventoryAlertController::class,
        'custom' => function() {
            Route::get('settings', [InventoryAlertController::class, 'settings']);
            Route::post('settings', [InventoryAlertController::class, 'saveSetting']);
            Route::delete('settings/{id}', [InventoryAlertController::class, 'deleteSetting']);
            Route::get('alerts', [InventoryAlertController::class, 'alerts']);
            Route::get('alerts/summary', [InventoryAlertController::class, 'summary']);
            Route::post('alerts/trigger-requests', [InventoryAlertController::class, 'triggerAutoRequests']);
        }
    ],
    'purchase-requests' => [
        'controller' => PurchaseRequestController::class,
        'custom' => function() {
            Route::get('summary', [PurchaseRequestController::class, 'summary']);
            Route::apiResource('/', PurchaseRequestController::class)->parameters(['' => 'id']);
            Route::post('{id}/approve', [PurchaseRequestController::class, 'approve']);
            Route::post('{id}/reject', [PurchaseRequestController::class, 'reject']);
            Route::post('{id}/mark-ordered', [PurchaseRequestController::class, 'markOrdered']);
            Route::post('{id}/complete', [PurchaseRequestController::class, 'complete']);
            Route::post('{id}/cancel', [PurchaseRequestController::class, 'cancel']);
        }
    ],
    'transfers' => [
        'controller' => TransferController::class,
        'custom' => function() {
            Route::apiResource('/', TransferController::class)->except(['update'])->parameters(['' => 'id']);
            Route::post('{id}/ship', [TransferController::class, 'ship']);
            Route::post('{id}/receive', [TransferController::class, 'receive']);
            Route::post('{id}/cancel', [TransferController::class, 'cancel']);
        }
    ],
    'expenses' => [
        'controller' => ExpenseController::class,
        'custom' => function() {
            Route::get('categories', [ExpenseController::class, 'categories']);
            Route::post('categories', [ExpenseController::class, 'createCategory']);
            Route::put('categories/{id}', [ExpenseController::class, 'updateCategory']);
            Route::delete('categories/{id}', [ExpenseController::class, 'deleteCategory']);
            Route::get('summary', [ExpenseController::class, 'summary']);
            Route::apiResource('/', ExpenseController::class)->parameters(['' => 'id']);
        }
    ],
    'reports' => [
        'controller' => ReportController::class,
        'custom' => function() {
            Route::get('dashboard', [ReportController::class, 'dashboardAnalytics']);
            Route::get('pnl', [ReportController::class, 'pnl']);
            Route::get('sales', [ReportController::class, 'sales']);
            Route::get('inventory', [ReportController::class, 'inventory']);
        }
    ],
    'cod-reconciliations' => [
        'controller' => CodReconciliationController::class,
        'custom' => function() {
            Route::get('shipping-partners', [CodReconciliationController::class, 'shippingPartners']);
            Route::apiResource('/', CodReconciliationController::class)->except(['update'])->parameters(['' => 'id']);
            Route::put('{id}/items', [CodReconciliationController::class, 'updateItems']);
            Route::post('{id}/reconcile', [CodReconciliationController::class, 'reconcile']);
        }
    ],
    'returns' => [
        'controller' => ReturnController::class,
        'custom' => function() {
            Route::apiResource('/', ReturnController::class)->only(['index', 'store', 'show'])->parameters(['' => 'id']);
            Route::post('{id}/approve', [ReturnController::class, 'approve']);
            Route::post('{id}/reject', [ReturnController::class, 'reject']);
            Route::put('{id}/receive', [ReturnController::class, 'receiveItems']);
            Route::post('{id}/complete', [ReturnController::class, 'complete']);
            Route::post('{id}/cancel', [ReturnController::class, 'cancel']);
        }
    ],
    'membership' => [
        'controller' => MembershipController::class,
        'custom' => function() {
            Route::get('tiers', [MembershipController::class, 'tiers']);
            Route::post('tiers', [MembershipController::class, 'createTier']);
            Route::put('tiers/{id}', [MembershipController::class, 'updateTier']);
            Route::delete('tiers/{id}', [MembershipController::class, 'deleteTier']);
            Route::get('customers/{customerId}', [MembershipController::class, 'customerMembership']);
            Route::get('customers/{customerId}/transactions', [MembershipController::class, 'transactions']);
            Route::post('customers/{customerId}/redeem', [MembershipController::class, 'redeem']);
            Route::post('calculate-discount', [MembershipController::class, 'calculateDiscount']);
        }
    ],
    'automations' => [
        'controller' => AutomationController::class,
        'custom' => function() {
            Route::get('/', [AutomationController::class, 'index']);
            Route::post('/', [AutomationController::class, 'store']);
            Route::put('{id}', [AutomationController::class, 'update']);
            Route::delete('{id}', [AutomationController::class, 'destroy']);
            Route::post('{id}/toggle', [AutomationController::class, 'toggle']);
        }
    ],
    'suppliers' => ['controller' => SupplierController::class, 'resource' => true],
    'articles' => [
        'controller' => ArticleController::class,
        'resource' => true,
        'custom' => function() {
            Route::post('{article}/publish', [ArticleController::class, 'publish']);
            Route::post('{article}/unpublish', [ArticleController::class, 'unpublish']);
        }
    ],
    'reviews' => [
        'controller' => ReviewController::class,
        'custom' => function() {
            Route::get('/', [ReviewController::class, 'getAllReviews']);
            Route::get('{review}', [ReviewController::class, 'showReview']);
            Route::put('{review}/approve', [ReviewController::class, 'approve']);
            Route::put('{review}/reject', [ReviewController::class, 'reject']);
            Route::delete('{review}', [ReviewController::class, 'destroy']);
        }
    ],
    'translate' => [
        'controller' => TranslationController::class,
        'custom' => function() {
            Route::post('/', [TranslationController::class, 'translate']);
            Route::get('/', [TranslationController::class, 'getTranslation']);
            Route::post('/batch', [TranslationController::class, 'translateBatch']);
            Route::post('/all', [TranslationController::class, 'translateAll']);
        }
    ],
    'settings' => [
        'controller' => SettingsController::class,
        'custom' => function() {
            Route::get('/', [SettingsController::class, 'index']);
            Route::get('{group}', [SettingsController::class, 'getByGroup']);
            Route::put('{group}', [SettingsController::class, 'update']);
            Route::get('{group}/{key}', [SettingsController::class, 'getSetting']);
        }
    ],
    'quotations' => [
        'controller' => QuotationController::class,
        'resource' => true,
        'except' => ['update'], // define custom update below
        'custom' => function() {
            Route::put('{id}', [QuotationController::class, 'update']);
            Route::post('{id}/send', [QuotationController::class, 'send']);
            Route::post('{id}/convert', [QuotationController::class, 'convertToOrder']);
        }
    ],
    'shipping' => [
        'controller' => ShippingController::class,
        'custom' => function() {
            Route::prefix('partners')->group(function() {
                Route::get('/', [ShippingController::class, 'index']);
                Route::post('/', [ShippingController::class, 'store']);
                Route::get('{id}', [ShippingController::class, 'show']);
                Route::put('{id}', [ShippingController::class, 'update']);
                Route::post('{id}/toggle', [ShippingController::class, 'toggle']);
            });
            Route::post('calculate-fee', [ShippingController::class, 'calculateFee']);
        }
    ],
    'employees' => [
        'controller' => EmployeeController::class,
        'resource' => true,
        'custom' => function() {
            Route::get('attendance-report', [EmployeeController::class, 'attendanceReport']);
            Route::post('{id}/check-in', [EmployeeController::class, 'checkIn']);
            Route::post('{id}/check-out', [EmployeeController::class, 'checkOut']);
        }
    ],
    'tasks' => [
        'controller' => TaskController::class,
        'resource' => true,
        'except' => ['show'],
        'custom' => function() {
            Route::get('board', [TaskController::class, 'board']);
            Route::patch('{id}/status', [TaskController::class, 'updateStatus']);
        }
    ],
    'appointments' => ['controller' => AppointmentController::class, 'resource' => true, 'except' => ['show']],
    'warranties' => [
        'controller' => WarrantyController::class,
        'custom' => function() {
            Route::get('/', [WarrantyController::class, 'index']);
            Route::get('lookup', [WarrantyController::class, 'lookup']);
            Route::post('/', [WarrantyController::class, 'store']);
            Route::post('{id}/claims', [WarrantyController::class, 'createClaim']);
            Route::put('claims/{id}', [WarrantyController::class, 'resolveClaim']);
        }
    ],
    'api-logs' => [
        'controller' => ApiLogController::class,
        'custom' => function() {
            Route::get('/', [ApiLogController::class, 'index']);
            Route::get('stats', [ApiLogController::class, 'stats']);
            Route::get('{id}', [ApiLogController::class, 'show']);
            Route::delete('cleanup', [ApiLogController::class, 'cleanup']);
        }
    ],
    'import-export' => [
        'controller' => ImportExportController::class,
        'custom' => function() {
            Route::get('export/products', [ImportExportController::class, 'exportProducts']);
            Route::get('export/orders', [ImportExportController::class, 'exportOrders']);
            Route::post('import/products', [ImportExportController::class, 'importProducts']);
            Route::get('template/{type}', [ImportExportController::class, 'downloadTemplate']);
        }
    ],
    'email' => [
        'controller' => EmailController::class,
        'custom' => function() {
            Route::prefix('campaigns')->group(function() {
                Route::apiResource('/', EmailController::class)->parameters(['' => 'id']);
                Route::post('{id}/send', [EmailController::class, 'send']);
            });
            Route::get('templates', [EmailController::class, 'templates']);
        }
    ],
    'roles' => [
        'controller' => RoleController::class,
        'custom' => function() {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::put('{id}', [RoleController::class, 'update']);
            Route::delete('{id}', [RoleController::class, 'destroy']);
            Route::put('{id}/permissions', [RoleController::class, 'updatePermissions']);
        }
    ],
    'audit-logs' => [
        'controller' => AuditLogController::class,
        'custom' => function() {
            Route::get('/', [AuditLogController::class, 'index']);
            Route::get('{id}', [AuditLogController::class, 'show']);
        }
    ]
];

// Register Routes
foreach ($modules as $uri => $cfg) {
    // Register custom routes BEFORE resource routes to avoid conflicts (e.g. /warehouses/dashboard-stats matching /warehouses/{id})
    if (isset($cfg['custom'])) {
        Route::prefix($uri)->group($cfg['custom']);
    }
    
    if (isset($cfg['resource']) && $cfg['resource']) {
        Route::apiResource($uri, $cfg['controller'], array_intersect_key($cfg, array_flip(['only', 'except'])));
    }
}



