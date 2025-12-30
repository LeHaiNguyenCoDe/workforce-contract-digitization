<?php

use App\Http\Controllers\Modules\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Public auth routes
Route::post('login', [AuthController::class, 'login']);
Route::get('me', [AuthController::class, 'me']);

// Protected auth routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
});
