<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Store\CategoryController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\ReviewController;
use App\Http\Controllers\Store\ArticleController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\WishlistController;
use App\Http\Controllers\Store\LoyaltyController;
use App\Http\Controllers\Store\WarehouseController;
use App\Http\Controllers\Store\PromotionController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;

// Public routes (không cần authentication)
Route::prefix('v1')->middleware([StartSession::class])->group(function () {
    // Đặt tên route là "login" để Laravel có thể redirect khi chưa xác thực
    // (tránh lỗi "Route [login] not defined" trong handler mặc định).
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('users', [UserController::class, 'store']); // Đăng ký user mới
});

// Protected routes with authentication middleware
Route::prefix('v1')->middleware([StartSession::class, Authenticate::class])->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::put('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);

    // Storefront APIs
    Route::prefix('store')->group(function () {
        // Categories & products
        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{category}/products', [ProductController::class, 'byCategory']);
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/{product}', [ProductController::class, 'show']);

        // Reviews
        Route::get('products/{product}/reviews', [ReviewController::class, 'index']);
        Route::post('products/{product}/reviews', [ReviewController::class, 'store']);

        // Articles (mẹo thiết kế)
        Route::get('articles', [ArticleController::class, 'index']);
        Route::get('articles/{article}', [ArticleController::class, 'show']);

        // Cart
        Route::get('cart', [CartController::class, 'show']);
        Route::post('cart/items', [CartController::class, 'addItem']);
        Route::put('cart/items/{item}', [CartController::class, 'updateItem']);
        Route::delete('cart/items/{item}', [CartController::class, 'removeItem']);

        // Orders (đặt hàng)
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::post('orders', [OrderController::class, 'store']);

        // Wishlist
        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist', [WishlistController::class, 'store']);
        Route::delete('wishlist/{product}', [WishlistController::class, 'destroy']);

        // Loyalty (tài khoản điểm)
        Route::get('loyalty', [LoyaltyController::class, 'show']);

        // Warehouses & Stocks (inventory cơ bản)
        Route::get('warehouses', [WarehouseController::class, 'index']);
        Route::post('warehouses', [WarehouseController::class, 'store']);
        Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show']);
        Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update']);
        Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy']);
        Route::get('warehouses/{warehouse}/stocks', [WarehouseController::class, 'stocks']);

        // Promotions
        Route::get('promotions', [PromotionController::class, 'index']);
        Route::post('promotions', [PromotionController::class, 'store']);
        Route::get('promotions/{promotion}', [PromotionController::class, 'show']);
        Route::put('promotions/{promotion}', [PromotionController::class, 'update']);
        Route::delete('promotions/{promotion}', [PromotionController::class, 'destroy']);
    });
});

