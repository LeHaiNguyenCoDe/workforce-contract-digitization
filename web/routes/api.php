<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Store\ArticleController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CategoryController;
use App\Http\Controllers\Store\LoyaltyController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\PromotionController;
use App\Http\Controllers\Store\ReviewController;
use App\Http\Controllers\Store\SupplierController;
use App\Http\Controllers\Store\WarehouseController;
use App\Http\Controllers\Store\WishlistController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Cấu trúc API được tổ chức như sau:
| - Frontend: Routes cho landing page và customer (public + authenticated)
| - Admin: Routes cho admin panel (cần admin/manager role)
|
*/

Route::prefix('v1')->middleware([StartSession::class])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PUBLIC ROUTES (Frontend - Landing Page)
    |--------------------------------------------------------------------------
    | Các routes công khai, không cần authentication
    */
    Route::group(['prefix' => 'frontend'], function () {
        // Language
        Route::get('language', [LanguageController::class, 'current']);
        Route::get('language/supported', [LanguageController::class, 'supported']);

        // Authentication
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('register', [UserController::class, 'store']); // Đăng ký user mới

        // Products & Categories (Public browsing)
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{category}', [CategoryController::class, 'show']);
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::get('categories/{category}/products', [ProductController::class, 'byCategory']);

        // Reviews (Public - chỉ xem)
        Route::get('products/{product}/reviews', [ReviewController::class, 'index']);

        // Articles/Blog (Public)
        Route::get('articles', [ArticleController::class, 'index']);
        Route::get('articles/{article}', [ArticleController::class, 'show']);

        // Promotions (Public - chỉ xem)
        Route::get('promotions', [PromotionController::class, 'index']);
        Route::get('promotions/{promotion}', [PromotionController::class, 'show']);

        // Cart (Session-based, không cần login)
        Route::get('cart', [CartController::class, 'show']);
        Route::post('cart/items', [CartController::class, 'addItem']);
        Route::put('cart/items/{item}', [CartController::class, 'updateItem']);
        Route::delete('cart/items/{item}', [CartController::class, 'removeItem']);
    });

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATED ROUTES (Frontend - Customer)
    |--------------------------------------------------------------------------
    | Các routes cần đăng nhập, dành cho customer
    */
    Route::group(['prefix' => 'frontend', 'middleware' => [Authenticate::class]], function () {
        // User Profile
        Route::get('me', [AuthController::class, 'me']);
        Route::get('profile', [UserController::class, 'show']); // Get own profile
        Route::put('profile', [UserController::class, 'update']); // Update own profile

        // Orders (Customer)
        Route::get('orders', [OrderController::class, 'index']); // My orders
        Route::post('orders', [OrderController::class, 'store']); // Create order
        Route::get('orders/{order}', [OrderController::class, 'show']); // Order details

        // Wishlist
        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist', [WishlistController::class, 'store']);
        Route::delete('wishlist/{product}', [WishlistController::class, 'destroy']);

        // Loyalty Program
        Route::get('loyalty', [LoyaltyController::class, 'show']);

        // Language Preference
        Route::post('language', [LanguageController::class, 'set']);

        // Reviews (Customer can create)
        Route::post('products/{product}/reviews', [ReviewController::class, 'store']);
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    | Các routes dành cho admin/manager để quản lý hệ thống
    | Yêu cầu: Authentication + Admin/Manager role
    */
    Route::group([
        'prefix' => 'admin',
        'middleware' => [Authenticate::class, AdminMiddleware::class]
    ], function () {

        // Dashboard & Statistics (có thể thêm sau)
        // Route::get('dashboard', [Admin\DashboardController::class, 'index']);

        // User Management
        Route::apiResource('users', UserController::class);
        Route::get('users/{user}/orders', [UserController::class, 'orders']); // Get user's orders

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
        Route::get('orders', [OrderController::class, 'index']); // All orders
        Route::get('orders/{order}', [OrderController::class, 'show']); // Order details
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus']); // Update order status
        Route::put('orders/{order}/cancel', [OrderController::class, 'cancel']); // Cancel order
        Route::get('orders/{order}/check-stock', [OrderController::class, 'checkStock']); 
        Route::post('orders/{order}/assign-shipper', [OrderController::class, 'assignShipper']);
        Route::put('orders/{order}/tracking', [OrderController::class, 'updateTracking']);

        // Promotion Management
        Route::apiResource('promotions', PromotionController::class);
        Route::post('promotions/{promotion}/items', [PromotionController::class, 'addItem']);
        Route::delete('promotions/{promotion}/items/{item}', [PromotionController::class, 'removeItem']);

        // Warehouse Management
        Route::get('warehouses/dashboard-stats', [WarehouseController::class, 'dashboardStats']);
        
        // Inbound Receipt Management (Phiếu nhập)
        Route::get('warehouses/inbound-receipts', [WarehouseController::class, 'inboundReceipts']);
        Route::post('warehouses/inbound-receipts', [WarehouseController::class, 'createInboundReceipt']);
        Route::put('warehouses/inbound-receipts/{id}', [WarehouseController::class, 'updateInboundReceipt']);
        Route::post('warehouses/inbound-receipts/{id}/approve', [WarehouseController::class, 'approveInboundReceipt']);
        Route::post('warehouses/inbound-receipts/{id}/cancel', [WarehouseController::class, 'cancelInboundReceipt']);
        Route::delete('warehouses/inbound-receipts/{id}', [WarehouseController::class, 'deleteInboundReceipt']);
        
        // Inbound Batch Management (BR-02.1, BR-02.2)
        Route::get('warehouses/inbound-batches', [WarehouseController::class, 'inboundBatches']);
        Route::post('warehouses/inbound-batches', [WarehouseController::class, 'createInboundBatch']);
        Route::get('warehouses/inbound-batches/{id}', [WarehouseController::class, 'showInboundBatch']);
        Route::post('warehouses/inbound-batches/{id}/receive', [WarehouseController::class, 'receiveInboundBatch']);
        
        // Quality Check Management (BR-03.1)
        Route::get('warehouses/quality-checks', [WarehouseController::class, 'qualityChecks']);
        Route::post('warehouses/quality-checks', [WarehouseController::class, 'createQualityCheck']);
        Route::put('warehouses/quality-checks/{id}', [WarehouseController::class, 'updateQualityCheck']);
        Route::delete('warehouses/quality-checks/{id}', [WarehouseController::class, 'deleteQualityCheck']);
        
        // Outbound Receipt Management (Phiếu xuất)
        Route::get('warehouses/outbound-receipts', [WarehouseController::class, 'outboundReceipts']);
        Route::post('warehouses/outbound-receipts', [WarehouseController::class, 'createOutboundReceipt']);
        Route::put('warehouses/outbound-receipts/{id}', [WarehouseController::class, 'updateOutboundReceipt']);
        Route::post('warehouses/outbound-receipts/{id}/approve', [WarehouseController::class, 'approveOutboundReceipt']);
        Route::post('warehouses/outbound-receipts/{id}/complete', [WarehouseController::class, 'completeOutboundReceipt']);
        Route::post('warehouses/outbound-receipts/{id}/cancel', [WarehouseController::class, 'cancelOutboundReceipt']);
        
        // Stock Adjustments (Điều chỉnh tồn)
        Route::get('warehouses/stock-adjustments', [WarehouseController::class, 'stockAdjustments']);
        Route::post('warehouses/stock-adjustments', [WarehouseController::class, 'createStockAdjustment']);
        
        // Warehouse CRUD
        Route::apiResource('warehouses', WarehouseController::class);
        
        // Stock Management
        Route::get('warehouses/{warehouse}/stocks', [WarehouseController::class, 'stocks']);
        Route::post('warehouses/{warehouse}/stocks/adjust', [WarehouseController::class, 'adjustStock']); // BR-05.1
        Route::post('warehouses/{warehouse}/stocks/outbound', [WarehouseController::class, 'outboundStock']); // BR-06.1
        
        // Inventory Logs (BR-09.2)
        Route::get('warehouses/{warehouse}/inventory-logs', [WarehouseController::class, 'inventoryLogs']);
        
        // Batch Management (FEFO)
        Route::get('batches/expiring-soon', [\App\Http\Controllers\BatchController::class, 'expiringSoon']);
        Route::get('batches/product/{productId}', [\App\Http\Controllers\BatchController::class, 'getProductBatches']);
        Route::get('batches', [\App\Http\Controllers\BatchController::class, 'index']);
        Route::post('batches', [\App\Http\Controllers\BatchController::class, 'store']);
        Route::get('batches/{id}', [\App\Http\Controllers\BatchController::class, 'show']);
        Route::put('batches/{id}', [\App\Http\Controllers\BatchController::class, 'update']);
        Route::delete('batches/{id}', [\App\Http\Controllers\BatchController::class, 'destroy']);
        
        // Stocktake Management (Kiểm kê)
        Route::get('stocktakes', [\App\Http\Controllers\StocktakeController::class, 'index']);
        Route::post('stocktakes', [\App\Http\Controllers\StocktakeController::class, 'store']);
        Route::get('stocktakes/{id}', [\App\Http\Controllers\StocktakeController::class, 'show']);
        Route::post('stocktakes/{id}/start', [\App\Http\Controllers\StocktakeController::class, 'start']);
        Route::put('stocktakes/{id}/items', [\App\Http\Controllers\StocktakeController::class, 'updateItems']);
        Route::post('stocktakes/{id}/complete', [\App\Http\Controllers\StocktakeController::class, 'complete']);
        Route::post('stocktakes/{id}/approve', [\App\Http\Controllers\StocktakeController::class, 'approve']);
        Route::post('stocktakes/{id}/cancel', [\App\Http\Controllers\StocktakeController::class, 'cancel']);
        Route::delete('stocktakes/{id}', [\App\Http\Controllers\StocktakeController::class, 'destroy']);
        
        // Inventory Alerts & Settings
        Route::get('inventory/settings', [\App\Http\Controllers\InventoryAlertController::class, 'settings']);
        Route::post('inventory/settings', [\App\Http\Controllers\InventoryAlertController::class, 'saveSetting']);
        Route::delete('inventory/settings/{id}', [\App\Http\Controllers\InventoryAlertController::class, 'deleteSetting']);
        Route::get('inventory/alerts', [\App\Http\Controllers\InventoryAlertController::class, 'alerts']);
        Route::get('inventory/alerts/summary', [\App\Http\Controllers\InventoryAlertController::class, 'summary']);
        Route::post('inventory/alerts/trigger-requests', [\App\Http\Controllers\InventoryAlertController::class, 'triggerAutoRequests']);
        
        // Purchase Requests
        Route::get('purchase-requests/summary', [\App\Http\Controllers\PurchaseRequestController::class, 'summary']);
        Route::get('purchase-requests', [\App\Http\Controllers\PurchaseRequestController::class, 'index']);
        Route::post('purchase-requests', [\App\Http\Controllers\PurchaseRequestController::class, 'store']);
        Route::get('purchase-requests/{id}', [\App\Http\Controllers\PurchaseRequestController::class, 'show']);
        Route::put('purchase-requests/{id}', [\App\Http\Controllers\PurchaseRequestController::class, 'update']);
        Route::post('purchase-requests/{id}/approve', [\App\Http\Controllers\PurchaseRequestController::class, 'approve']);
        Route::post('purchase-requests/{id}/reject', [\App\Http\Controllers\PurchaseRequestController::class, 'reject']);
        Route::post('purchase-requests/{id}/mark-ordered', [\App\Http\Controllers\PurchaseRequestController::class, 'markOrdered']);
        Route::post('purchase-requests/{id}/complete', [\App\Http\Controllers\PurchaseRequestController::class, 'complete']);
        Route::post('purchase-requests/{id}/cancel', [\App\Http\Controllers\PurchaseRequestController::class, 'cancel']);
        Route::delete('purchase-requests/{id}', [\App\Http\Controllers\PurchaseRequestController::class, 'destroy']);
        
        // Transfer Management (Chuyển kho)
        Route::get('transfers', [\App\Http\Controllers\TransferController::class, 'index']);
        Route::post('transfers', [\App\Http\Controllers\TransferController::class, 'store']);
        Route::get('transfers/{id}', [\App\Http\Controllers\TransferController::class, 'show']);
        Route::post('transfers/{id}/ship', [\App\Http\Controllers\TransferController::class, 'ship']);
        Route::post('transfers/{id}/receive', [\App\Http\Controllers\TransferController::class, 'receive']);
        Route::post('transfers/{id}/cancel', [\App\Http\Controllers\TransferController::class, 'cancel']);
        Route::delete('transfers/{id}', [\App\Http\Controllers\TransferController::class, 'destroy']);
        
        // Finance - Expense Management
        Route::get('expenses/categories', [\App\Http\Controllers\ExpenseController::class, 'categories']);
        Route::post('expenses/categories', [\App\Http\Controllers\ExpenseController::class, 'createCategory']);
        Route::get('expenses/summary', [\App\Http\Controllers\ExpenseController::class, 'summary']);
        Route::get('expenses', [\App\Http\Controllers\ExpenseController::class, 'index']);
        Route::post('expenses', [\App\Http\Controllers\ExpenseController::class, 'store']);
        Route::get('expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'show']);
        Route::put('expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'update']);
        Route::delete('expenses/{id}', [\App\Http\Controllers\ExpenseController::class, 'destroy']);
        
        // Reports
        Route::get('reports/pnl', [\App\Http\Controllers\ReportController::class, 'pnl']);
        Route::get('reports/sales', [\App\Http\Controllers\ReportController::class, 'sales']);
        Route::get('reports/inventory', [\App\Http\Controllers\ReportController::class, 'inventory']);
        
        // COD Reconciliation
        Route::get('cod-reconciliations/shipping-partners', [\App\Http\Controllers\CodReconciliationController::class, 'shippingPartners']);
        Route::get('cod-reconciliations', [\App\Http\Controllers\CodReconciliationController::class, 'index']);
        Route::post('cod-reconciliations', [\App\Http\Controllers\CodReconciliationController::class, 'store']);
        Route::get('cod-reconciliations/{id}', [\App\Http\Controllers\CodReconciliationController::class, 'show']);
        Route::put('cod-reconciliations/{id}/items', [\App\Http\Controllers\CodReconciliationController::class, 'updateItems']);
        Route::post('cod-reconciliations/{id}/reconcile', [\App\Http\Controllers\CodReconciliationController::class, 'reconcile']);
        Route::delete('cod-reconciliations/{id}', [\App\Http\Controllers\CodReconciliationController::class, 'destroy']);
        
        // Returns/RMA
        Route::get('returns', [\App\Http\Controllers\ReturnController::class, 'index']);
        Route::post('returns', [\App\Http\Controllers\ReturnController::class, 'store']);
        Route::get('returns/{id}', [\App\Http\Controllers\ReturnController::class, 'show']);
        Route::post('returns/{id}/approve', [\App\Http\Controllers\ReturnController::class, 'approve']);
        Route::post('returns/{id}/reject', [\App\Http\Controllers\ReturnController::class, 'reject']);
        Route::put('returns/{id}/receive', [\App\Http\Controllers\ReturnController::class, 'receiveItems']);
        Route::post('returns/{id}/complete', [\App\Http\Controllers\ReturnController::class, 'complete']);
        Route::post('returns/{id}/cancel', [\App\Http\Controllers\ReturnController::class, 'cancel']);
        
        // Membership & Points
        Route::get('membership/tiers', [\App\Http\Controllers\MembershipController::class, 'tiers']);
        Route::post('membership/tiers', [\App\Http\Controllers\MembershipController::class, 'createTier']);
        Route::put('membership/tiers/{id}', [\App\Http\Controllers\MembershipController::class, 'updateTier']);
        Route::delete('membership/tiers/{id}', [\App\Http\Controllers\MembershipController::class, 'deleteTier']);
        Route::get('membership/customers/{customerId}', [\App\Http\Controllers\MembershipController::class, 'customerMembership']);
        Route::get('membership/customers/{customerId}/transactions', [\App\Http\Controllers\MembershipController::class, 'transactions']);
        Route::post('membership/customers/{customerId}/redeem', [\App\Http\Controllers\MembershipController::class, 'redeem']);
        Route::post('membership/calculate-discount', [\App\Http\Controllers\MembershipController::class, 'calculateDiscount']);
        
        Route::apiResource('suppliers', SupplierController::class);

        // Article/Blog Management
        Route::apiResource('articles', ArticleController::class);
        Route::post('articles/{article}/publish', [ArticleController::class, 'publish']);
        Route::post('articles/{article}/unpublish', [ArticleController::class, 'unpublish']);

        // Review Management
        Route::get('reviews', [ReviewController::class, 'getAllReviews']); // All reviews
        Route::get('reviews/{review}', [ReviewController::class, 'showReview']);
        Route::put('reviews/{review}/approve', [ReviewController::class, 'approve']);
        Route::put('reviews/{review}/reject', [ReviewController::class, 'reject']);
        Route::delete('reviews/{review}', [ReviewController::class, 'destroy']);

        // Statistics & Reports (có thể thêm sau)
        // Route::get('statistics/sales', [Admin\StatisticsController::class, 'sales']);
        // Route::get('statistics/products', [Admin\StatisticsController::class, 'products']);
        // Route::get('statistics/users', [Admin\StatisticsController::class, 'users']);
    });
});

/*
|--------------------------------------------------------------------------
| BACKWARD COMPATIBILITY
|--------------------------------------------------------------------------
| Giữ lại các routes cũ để không break existing clients
| Có thể remove sau khi frontend đã update
*/
Route::prefix('v1')->middleware([StartSession::class])->group(function () {
    // Public routes (backward compatibility)
    Route::get('language', [LanguageController::class, 'current']);
    Route::get('language/supported', [LanguageController::class, 'supported']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('users', [UserController::class, 'store']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::get('categories/{category}/products', [ProductController::class, 'byCategory']);
    Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{article}', [ArticleController::class, 'show']);
    Route::get('cart', [CartController::class, 'show']);
    Route::post('cart/items', [CartController::class, 'addItem']);
    Route::put('cart/items/{item}', [CartController::class, 'updateItem']);
    Route::delete('cart/items/{item}', [CartController::class, 'removeItem']);
    Route::get('promotions', [PromotionController::class, 'index']);
    Route::get('promotions/{promotion}', [PromotionController::class, 'show']);

    // Protected routes (backward compatibility)
    Route::middleware([Authenticate::class])->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::get('users', [UserController::class, 'index']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);
        Route::get('orders', [OrderController::class, 'index']);
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist', [WishlistController::class, 'store']);
        Route::delete('wishlist/{product}', [WishlistController::class, 'destroy']);
        Route::get('loyalty', [LoyaltyController::class, 'show']);
        Route::post('language', [LanguageController::class, 'set']);
        Route::post('products/{product}/reviews', [ReviewController::class, 'store']);

        // Admin routes (backward compatibility)
        Route::post('promotions', [PromotionController::class, 'store']);
        Route::put('promotions/{promotion}', [PromotionController::class, 'update']);
        Route::delete('promotions/{promotion}', [PromotionController::class, 'destroy']);
        Route::get('warehouses', [WarehouseController::class, 'index']);
        Route::post('warehouses', [WarehouseController::class, 'store']);
        Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show']);
        Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update']);
        Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy']);
        Route::get('warehouses/{warehouse}/stocks', [WarehouseController::class, 'stocks']);
    });
});
