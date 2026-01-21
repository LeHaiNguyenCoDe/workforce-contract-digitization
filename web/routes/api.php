<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
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
        'middleware' => [Authenticate::class, AdminMiddleware::class]
    ],
    'chat.php' => [
        'prefix' => '',
        'middleware' => [Authenticate::class]
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

    // Custom broadcasting auth route (keep inside v1 group)
    Route::middleware([Authenticate::class])->group(function () {
        Route::post('broadcasting/auth', function (\Illuminate\Http\Request $request) {
            // ... (keep the existing logic for broadcasting auth)
            $user = $request->user();
            $channelName = $request->channel_name;

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $normalizedName = $channelName;
            $isPresence = false;
            if (str_starts_with($channelName, 'private-')) {
                $normalizedName = substr($channelName, 8);
            } elseif (str_starts_with($channelName, 'presence-')) {
                $normalizedName = substr($channelName, 9);
                $isPresence = true;
            }

            $authorized = false;
            $presenceData = null;

            if (preg_match('/^user\.(\d+)$/', $normalizedName, $matches)) {
                if ((int) $user->id === (int) $matches[1]) { $authorized = true; }
            } elseif (preg_match('/^conversation\.(\d+)$/', $normalizedName, $matches)) {
                $authorized = \DB::table('conversation_user')->where('conversation_id', (int)$matches[1])->where('user_id', $user->id)->exists();
            } elseif (preg_match('/^presence\.conversation\.(\d+)$/', $normalizedName, $matches)) {
                $authorized = \DB::table('conversation_user')->where('conversation_id', (int)$matches[1])->where('user_id', $user->id)->exists();
                if ($authorized) { $presenceData = ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar]; }
            } elseif ($normalizedName === 'admin.guest-chats') {
                $adminRoles = ['admin', 'manager', 'super_admin'];
                $authorized = in_array($user->role, $adminRoles);
            }

            if ($authorized) {
                $socketId = $request->socket_id;
                $appKey = config('broadcasting.connections.reverb.key');
                $appSecret = config('broadcasting.connections.reverb.secret');
                $stringToSign = $socketId . ':' . $channelName;
                if ($isPresence && $presenceData) {
                    $channelData = json_encode(['user_id' => (string)$presenceData['id'], 'user_info' => $presenceData]);
                    $stringToSign .= ':' . $channelData;
                }
                $signature = hash_hmac('sha256', $stringToSign, $appSecret);
                $response = ['auth' => $appKey . ':' . $signature];
                if ($isPresence && $presenceData) { $response['channel_data'] = $channelData; }
                return response()->json($response);
            }

            return response()->json(['message' => 'Forbidden'], 403);
        });
    });
});

/*
|--------------------------------------------------------------------------
| BACKWARD COMPATIBILITY (Legacy Routes)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
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
