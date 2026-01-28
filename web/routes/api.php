<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\DeprecatedRoutes;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes Configuration
|--------------------------------------------------------------------------
| Define how each route file in routes/api/ should be loaded.
*/

$v1Groups = [
    'auth.php' => ['prefix' => 'frontend', 'middleware' => []],
    'landing.php' => ['prefix' => 'frontend', 'middleware' => []],
    'admin.php' => [
        'prefix' => 'admin',
        'middleware' => [Authenticate::class, AdminMiddleware::class, 'throttle:admin']
    ],
    'chat.php' => [
        'prefix' => '',
        'middleware' => [Authenticate::class, 'throttle:chat']
    ],
    'marketing.php' => [
        'prefix' => 'marketing',
        'middleware' => [Authenticate::class, 'throttle:api']
    ],
];

Route::prefix('v1')->group(function () use ($v1Groups) {
    foreach ($v1Groups as $file => $config) {
        $filePath = __DIR__ . '/api/' . $file;
        if (file_exists($filePath)) {
            Route::prefix($config['prefix'])
                ->middleware($config['middleware'])
                ->group($filePath);
        }
    }

    // Broadcasting authentication route - moved to dedicated controller
    Route::middleware([Authenticate::class])->group(function () {
        Route::post('broadcasting/auth', [\App\Http\Controllers\BroadcastingController::class, 'authenticate']);
    });
});

/*
|--------------------------------------------------------------------------
| BACKWARD COMPATIBILITY (Legacy Routes) - DEPRECATED
|--------------------------------------------------------------------------
| These routes are deprecated and will be removed after 90 days.
| Please migrate to the new modular structure:
|   - Use /api/v1/frontend/* for landing routes
|   - Use /api/v1/admin/* for admin routes
|
| TODO: Remove after [2026-04-26] (90 days from 2026-01-26)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware([DeprecatedRoutes::class])->group(function () {
    require __DIR__ . '/api/landing.php';

    Route::middleware([Authenticate::class, AdminMiddleware::class])->group(function () {
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
