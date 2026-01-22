<?php

use App\Http\Controllers\Modules\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public auth routes - with rate limiting for security
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:login');
Route::get('me', [AuthController::class, 'me']);

// Protected auth routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});
