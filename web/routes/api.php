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
        Route::get('warehouses/quality-checks', [WarehouseController::class, 'qualityChecks']);
        Route::post('warehouses/quality-checks', [WarehouseController::class, 'storeQualityCheck']);
        Route::put('warehouses/quality-checks/{id}', [WarehouseController::class, 'updateQualityCheck']);
        Route::delete('warehouses/quality-checks/{id}', [WarehouseController::class, 'deleteQualityCheck']);
        Route::apiResource('warehouses', WarehouseController::class);
        Route::get('warehouses/{warehouse}/stocks', [WarehouseController::class, 'stocks']);
        Route::post('warehouses/{warehouse}/stocks', [WarehouseController::class, 'updateStock']);
        Route::get('warehouses/{warehouse}/movements', [WarehouseController::class, 'stockMovements']);
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
