<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

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

    /*
    |--------------------------------------------------------------------------
    | Chat, Friends & Notifications Routes (Authenticated Users)
    |--------------------------------------------------------------------------
    */
    Route::middleware([Authenticate::class])->group(function () {
        require __DIR__ . '/api/chat.php';
        
        // Custom broadcasting auth route to use API guard
        Route::post('broadcasting/auth', function (\Illuminate\Http\Request $request) {
            $user = $request->user();
            $channelName = $request->channel_name;
            
            \Log::info('Broadcasting Auth (Manual)', [
                'user_id' => $user?->id,
                'channel' => $channelName
            ]);

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            // Normalize channel name (strip 'private-' or 'presence-')
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

            // user.{id}
            if (preg_match('/^user\.(\d+)$/', $normalizedName, $matches)) {
                if ((int)$user->id === (int)$matches[1]) {
                    $authorized = true;
                }
            }
            // conversation.{id}
            elseif (preg_match('/^conversation\.(\d+)$/', $normalizedName, $matches)) {
                $conversationId = (int)$matches[1];
                $authorized = \DB::table('conversation_user')
                    ->where('conversation_id', $conversationId)
                    ->where('user_id', $user->id)
                    ->exists();
            }
            // presence.conversation.{id}
            elseif (preg_match('/^presence\.conversation\.(\d+)$/', $normalizedName, $matches)) {
                $conversationId = (int)$matches[1];
                $authorized = \DB::table('conversation_user')
                    ->where('conversation_id', $conversationId)
                    ->where('user_id', $user->id)
                    ->exists();
                if ($authorized) {
                    $presenceData = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar
                    ];
                }
            }

            if ($authorized) {
                return Broadcast::validAuthenticationResponse(
                    $request, 
                    $isPresence ? $presenceData : true
                );
            }

            return response()->json(['message' => 'Forbidden'], 403);
        });
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
