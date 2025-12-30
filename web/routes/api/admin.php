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
use App\Http\Controllers\Modules\Landing\CodReconciliationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| All routes require Authentication + Admin/Manager role
*/

// User Management
Route::apiResource('users', UserController::class);
Route::get('users/{user}/orders', [UserController::class, 'orders']);

// Product Management
Route::apiResource('products', ProductController::class);
Route::post('products/{product}/images', [ProductController::class, 'addImage']);
Route::delete('products/{product}/images/{image}', [ProductController::class, 'removeImage']);
Route::post('products/{product}/variants', [ProductController::class, 'addVariant']);
Route::put('products/{product}/variants/{variant}', [ProductController::class, 'updateVariant']);
Route::delete('products/{product}/variants/{variant}', [ProductController::class, 'removeVariant']);

// Category Management
Route::apiResource('categories', CategoryController::class);

// Order Management
Route::get('orders', [OrderController::class, 'index']);
Route::get('orders/{order}', [OrderController::class, 'show']);
Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']);
Route::put('orders/{order}/cancel', [OrderController::class, 'cancel']);
Route::get('orders/{order}/check-stock', [OrderController::class, 'checkStock']);
Route::post('orders/{order}/assign-shipper', [OrderController::class, 'assignShipper']);
Route::put('orders/{order}/tracking', [OrderController::class, 'updateTracking']);
Route::post('orders/{order}/confirm', [OrderController::class, 'confirmOrder']);
Route::post('orders/{order}/deliver', [OrderController::class, 'markDelivered']);
Route::post('orders/{order}/complete', [OrderController::class, 'completeOrder']);
Route::post('orders/{order}/cancel', [OrderController::class, 'cancelOrder']);

// Finance Management
Route::prefix('finance')->group(function () {
    Route::get('funds', [FinanceController::class, 'getFunds']);
    Route::get('transactions', [FinanceController::class, 'getTransactions']);
    Route::post('transactions', [FinanceController::class, 'createTransaction']);
    Route::get('summary', [FinanceController::class, 'getSummary']);
});

// Debt Management
Route::prefix('debts')->group(function () {
    Route::get('receivables', [DebtController::class, 'getReceivables']);
    Route::get('receivables/summary', [DebtController::class, 'getReceivableSummary']);
    Route::post('receivables/{receivable}/pay', [DebtController::class, 'payReceivable']);
    Route::get('payables', [DebtController::class, 'getPayables']);
    Route::get('payables/summary', [DebtController::class, 'getPayableSummary']);
    Route::post('payables/{payable}/pay', [DebtController::class, 'payPayable']);
});

// Promotion Management
Route::apiResource('promotions', PromotionController::class);
Route::post('promotions/{promotion}/items', [PromotionController::class, 'addItem']);
Route::delete('promotions/{promotion}/items/{item}', [PromotionController::class, 'removeItem']);

// Warehouse Management
Route::prefix('warehouses')->group(function () {
    Route::get('dashboard-stats', [WarehouseController::class, 'dashboardStats']);
    
    // Inbound Receipts
    Route::get('inbound-receipts', [WarehouseController::class, 'inboundReceipts']);
    Route::post('inbound-receipts', [WarehouseController::class, 'createInboundReceipt']);
    Route::put('inbound-receipts/{id}', [WarehouseController::class, 'updateInboundReceipt']);
    Route::post('inbound-receipts/{id}/approve', [WarehouseController::class, 'approveInboundReceipt']);
    Route::post('inbound-receipts/{id}/cancel', [WarehouseController::class, 'cancelInboundReceipt']);
    Route::delete('inbound-receipts/{id}', [WarehouseController::class, 'deleteInboundReceipt']);
    
    // Inbound Batches
    Route::get('inbound-batches', [WarehouseController::class, 'inboundBatches']);
    Route::post('inbound-batches', [WarehouseController::class, 'createInboundBatch']);
    Route::get('inbound-batches/{id}', [WarehouseController::class, 'showInboundBatch']);
    Route::post('inbound-batches/{id}/receive', [WarehouseController::class, 'receiveInboundBatch']);
    
    // Quality Checks
    Route::get('quality-checks', [WarehouseController::class, 'qualityChecks']);
    Route::post('quality-checks', [WarehouseController::class, 'createQualityCheck']);
    Route::put('quality-checks/{id}', [WarehouseController::class, 'updateQualityCheck']);
    Route::delete('quality-checks/{id}', [WarehouseController::class, 'deleteQualityCheck']);
    
    // Outbound Receipts
    Route::get('outbound-receipts', [WarehouseController::class, 'outboundReceipts']);
    Route::post('outbound-receipts', [WarehouseController::class, 'createOutboundReceipt']);
    Route::put('outbound-receipts/{id}', [WarehouseController::class, 'updateOutboundReceipt']);
    Route::post('outbound-receipts/{id}/approve', [WarehouseController::class, 'approveOutboundReceipt']);
    Route::post('outbound-receipts/{id}/complete', [WarehouseController::class, 'completeOutboundReceipt']);
    Route::post('outbound-receipts/{id}/cancel', [WarehouseController::class, 'cancelOutboundReceipt']);
    
    // Stock Adjustments
    Route::get('stock-adjustments', [WarehouseController::class, 'stockAdjustments']);
    Route::post('stock-adjustments', [WarehouseController::class, 'createStockAdjustment']);
});

// Warehouse CRUD
Route::apiResource('warehouses', WarehouseController::class);
Route::get('warehouses/{warehouse}/stocks', [WarehouseController::class, 'stocks']);
Route::post('warehouses/{warehouse}/stocks/adjust', [WarehouseController::class, 'adjustStock']);
Route::post('warehouses/{warehouse}/stocks/outbound', [WarehouseController::class, 'outboundStock']);
Route::get('warehouses/{warehouse}/inventory-logs', [WarehouseController::class, 'inventoryLogs']);

// Batch Management
Route::prefix('batches')->group(function () {
    Route::get('expiring-soon', [BatchController::class, 'expiringSoon']);
    Route::get('product/{productId}', [BatchController::class, 'getProductBatches']);
    Route::get('/', [BatchController::class, 'index']);
    Route::post('/', [BatchController::class, 'store']);
    Route::get('{id}', [BatchController::class, 'show']);
    Route::put('{id}', [BatchController::class, 'update']);
    Route::delete('{id}', [BatchController::class, 'destroy']);
});

// Stocktake Management
Route::prefix('stocktakes')->group(function () {
    Route::get('/', [StocktakeController::class, 'index']);
    Route::post('/', [StocktakeController::class, 'store']);
    Route::get('{id}', [StocktakeController::class, 'show']);
    Route::post('{id}/start', [StocktakeController::class, 'start']);
    Route::put('{id}/items', [StocktakeController::class, 'updateItems']);
    Route::post('{id}/complete', [StocktakeController::class, 'complete']);
    Route::post('{id}/approve', [StocktakeController::class, 'approve']);
    Route::post('{id}/cancel', [StocktakeController::class, 'cancel']);
    Route::delete('{id}', [StocktakeController::class, 'destroy']);
});

// Inventory Alerts & Settings
Route::prefix('inventory')->group(function () {
    Route::get('settings', [InventoryAlertController::class, 'settings']);
    Route::post('settings', [InventoryAlertController::class, 'saveSetting']);
    Route::delete('settings/{id}', [InventoryAlertController::class, 'deleteSetting']);
    Route::get('alerts', [InventoryAlertController::class, 'alerts']);
    Route::get('alerts/summary', [InventoryAlertController::class, 'summary']);
    Route::post('alerts/trigger-requests', [InventoryAlertController::class, 'triggerAutoRequests']);
});

// Purchase Requests
Route::prefix('purchase-requests')->group(function () {
    Route::get('summary', [PurchaseRequestController::class, 'summary']);
    Route::get('/', [PurchaseRequestController::class, 'index']);
    Route::post('/', [PurchaseRequestController::class, 'store']);
    Route::get('{id}', [PurchaseRequestController::class, 'show']);
    Route::put('{id}', [PurchaseRequestController::class, 'update']);
    Route::post('{id}/approve', [PurchaseRequestController::class, 'approve']);
    Route::post('{id}/reject', [PurchaseRequestController::class, 'reject']);
    Route::post('{id}/mark-ordered', [PurchaseRequestController::class, 'markOrdered']);
    Route::post('{id}/complete', [PurchaseRequestController::class, 'complete']);
    Route::post('{id}/cancel', [PurchaseRequestController::class, 'cancel']);
    Route::delete('{id}', [PurchaseRequestController::class, 'destroy']);
});

// Transfer Management
Route::prefix('transfers')->group(function () {
    Route::get('/', [TransferController::class, 'index']);
    Route::post('/', [TransferController::class, 'store']);
    Route::get('{id}', [TransferController::class, 'show']);
    Route::post('{id}/ship', [TransferController::class, 'ship']);
    Route::post('{id}/receive', [TransferController::class, 'receive']);
    Route::post('{id}/cancel', [TransferController::class, 'cancel']);
    Route::delete('{id}', [TransferController::class, 'destroy']);
});

// Expense Management
Route::prefix('expenses')->group(function () {
    Route::get('categories', [ExpenseController::class, 'categories']);
    Route::post('categories', [ExpenseController::class, 'createCategory']);
    Route::put('categories/{id}', [ExpenseController::class, 'updateCategory']);
    Route::delete('categories/{id}', [ExpenseController::class, 'deleteCategory']);
    Route::get('summary', [ExpenseController::class, 'summary']);
    Route::get('/', [ExpenseController::class, 'index']);
    Route::post('/', [ExpenseController::class, 'store']);
    Route::get('{id}', [ExpenseController::class, 'show']);
    Route::put('{id}', [ExpenseController::class, 'update']);
    Route::delete('{id}', [ExpenseController::class, 'destroy']);
});

// Reports
Route::prefix('reports')->group(function () {
    Route::get('pnl', [ReportController::class, 'pnl']);
    Route::get('sales', [ReportController::class, 'sales']);
    Route::get('inventory', [ReportController::class, 'inventory']);
});

// COD Reconciliation
Route::prefix('cod-reconciliations')->group(function () {
    Route::get('shipping-partners', [CodReconciliationController::class, 'shippingPartners']);
    Route::get('/', [CodReconciliationController::class, 'index']);
    Route::post('/', [CodReconciliationController::class, 'store']);
    Route::get('{id}', [CodReconciliationController::class, 'show']);
    Route::put('{id}/items', [CodReconciliationController::class, 'updateItems']);
    Route::post('{id}/reconcile', [CodReconciliationController::class, 'reconcile']);
    Route::delete('{id}', [CodReconciliationController::class, 'destroy']);
});

// Returns/RMA
Route::prefix('returns')->group(function () {
    Route::get('/', [ReturnController::class, 'index']);
    Route::post('/', [ReturnController::class, 'store']);
    Route::get('{id}', [ReturnController::class, 'show']);
    Route::post('{id}/approve', [ReturnController::class, 'approve']);
    Route::post('{id}/reject', [ReturnController::class, 'reject']);
    Route::put('{id}/receive', [ReturnController::class, 'receiveItems']);
    Route::post('{id}/complete', [ReturnController::class, 'complete']);
    Route::post('{id}/cancel', [ReturnController::class, 'cancel']);
});

// Membership & Points
Route::prefix('membership')->group(function () {
    Route::get('tiers', [MembershipController::class, 'tiers']);
    Route::post('tiers', [MembershipController::class, 'createTier']);
    Route::put('tiers/{id}', [MembershipController::class, 'updateTier']);
    Route::delete('tiers/{id}', [MembershipController::class, 'deleteTier']);
    Route::get('customers/{customerId}', [MembershipController::class, 'customerMembership']);
    Route::get('customers/{customerId}/transactions', [MembershipController::class, 'transactions']);
    Route::post('customers/{customerId}/redeem', [MembershipController::class, 'redeem']);
    Route::post('calculate-discount', [MembershipController::class, 'calculateDiscount']);
});

// Suppliers
Route::apiResource('suppliers', SupplierController::class);

// Articles/Blog Management
Route::apiResource('articles', ArticleController::class);
Route::post('articles/{article}/publish', [ArticleController::class, 'publish']);
Route::post('articles/{article}/unpublish', [ArticleController::class, 'unpublish']);

// Review Management
Route::prefix('reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'getAllReviews']);
    Route::get('{review}', [ReviewController::class, 'showReview']);
    Route::put('{review}/approve', [ReviewController::class, 'approve']);
    Route::put('{review}/reject', [ReviewController::class, 'reject']);
    Route::delete('{review}', [ReviewController::class, 'destroy']);
});
