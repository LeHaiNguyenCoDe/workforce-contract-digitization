<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    /**
     * Xem tài khoản điểm & lịch sử giao dịch của user hiện tại.
     */
    public function show(Request $request): JsonResponse
    {
        $user = Auth::user();

        $account = LoyaltyAccount::firstOrCreate(
            ['user_id' => $user->id],
            ['points' => 0]
        );

        $account->load(['transactions' => function ($q) {
            $q->latest()->limit(50);
        }]);

        return response()->json([
            'data' => $account,
        ]);
    }
}


