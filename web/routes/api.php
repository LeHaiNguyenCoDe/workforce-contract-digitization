<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;

// Public routes (không cần authentication)
Route::prefix('v1')->middleware([StartSession::class])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
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
});

