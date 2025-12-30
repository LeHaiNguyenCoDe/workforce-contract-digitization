<?php

use App\Http\Controllers\Modules\Landing\CartController;
use App\Http\Controllers\Modules\Landing\LanguageController;
use App\Http\Controllers\Modules\Landing\LoyaltyController;
use App\Http\Controllers\Modules\Landing\WishlistController;
use App\Http\Controllers\Modules\Admin\ArticleController;
use App\Http\Controllers\Modules\Admin\CategoryController;
use App\Http\Controllers\Modules\Admin\ProductController;
use App\Http\Controllers\Modules\Admin\PromotionController;
use App\Http\Controllers\Modules\Admin\ReviewController;
use App\Http\Controllers\Modules\Admin\UserController;
use App\Http\Controllers\Modules\Admin\OrderController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing/Frontend Routes (Public + Authenticated Customer)
|--------------------------------------------------------------------------
*/

// Language
Route::get('language', [LanguageController::class, 'current']);
Route::get('language/supported', [LanguageController::class, 'supported']);

// Products & Categories (Public browsing)
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('categories/{category}/products', [ProductController::class, 'byCategory']);

// Reviews (Public - view only)
Route::get('products/{product}/reviews', [ReviewController::class, 'index']);

// Articles/Blog (Public)
Route::get('articles', [ArticleController::class, 'index']);
Route::get('articles/{article}', [ArticleController::class, 'show']);

// Promotions (Public - view only)
Route::get('promotions', [PromotionController::class, 'index']);
Route::get('promotions/{promotion}', [PromotionController::class, 'show']);

// Cart (Session-based, no login required)
Route::get('cart', [CartController::class, 'show']);
Route::post('cart/items', [CartController::class, 'addItem']);
Route::put('cart/items/{item}', [CartController::class, 'updateItem']);
Route::delete('cart/items/{item}', [CartController::class, 'removeItem']);

// Register
Route::post('register', [UserController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Authenticated Customer Routes
|--------------------------------------------------------------------------
*/
Route::middleware([Authenticate::class])->group(function () {
    // User Profile
    Route::get('profile', [UserController::class, 'show']);
    Route::put('profile', [UserController::class, 'update']);

    // Orders (Customer)
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);

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
