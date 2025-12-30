<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Cấu trúc API được tổ chức như sau:
| - Auth: Authentication routes
| - Frontend: Routes cho landing page và customer (public + authenticated)
| - Admin: Routes cho admin panel (cần admin/manager role)
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    */
    require __DIR__ . '/api/auth.php';

    /*
    |--------------------------------------------------------------------------
    | Frontend/Landing Routes (Public + Authenticated Customer)
    |--------------------------------------------------------------------------
    */
    Route::prefix('frontend')->group(function () {
        require __DIR__ . '/api/landing.php';
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Requires Authentication + Admin Role)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')
        ->middleware([Authenticate::class, AdminMiddleware::class])
        ->group(function () {
            require __DIR__ . '/api/admin.php';
        });
});

/*
|--------------------------------------------------------------------------
| BACKWARD COMPATIBILITY (Legacy Routes)
|--------------------------------------------------------------------------
| Giữ lại các routes cũ để không break existing clients
| Có thể remove sau khi frontend đã update sang v1/frontend hoặc v1/admin
*/
Route::prefix('v1')->group(function () {
    // Include landing routes again at root level for backward compatibility
    require __DIR__ . '/api/landing.php';
    
    // Protected legacy routes
    Route::middleware([Authenticate::class])->group(function () {
        // Legacy admin routes that were at root level
        Route::post('promotions', [\App\Http\Controllers\Modules\Admin\PromotionController::class, 'store']);
        Route::put('promotions/{promotion}', [\App\Http\Controllers\Modules\Admin\PromotionController::class, 'update']);
        Route::delete('promotions/{promotion}', [\App\Http\Controllers\Modules\Admin\PromotionController::class, 'destroy']);
        Route::get('warehouses', [\App\Http\Controllers\Modules\Admin\WarehouseController::class, 'index']);
        Route::post('warehouses', [\App\Http\Controllers\Modules\Admin\WarehouseController::class, 'store']);
        Route::get('warehouses/{warehouse}', [\App\Http\Controllers\Modules\Admin\WarehouseController::class, 'show']);
        Route::put('warehouses/{warehouse}', [\App\Http\Controllers\Modules\Admin\WarehouseController::class, 'update']);
        Route::delete('warehouses/{warehouse}', [\App\Http\Controllers\Modules\Admin\WarehouseController::class, 'destroy']);
        Route::get('warehouses/{warehouse}/stocks', [\App\Http\Controllers\Modules\Admin\WarehouseController::class, 'stocks']);
    });
});
