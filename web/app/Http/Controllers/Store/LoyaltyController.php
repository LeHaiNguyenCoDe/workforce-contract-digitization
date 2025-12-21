<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    public function __construct(
        private LoyaltyService $loyaltyService
    ) {
    }

    /**
     * Get loyalty account with transactions
     */
    public function show(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $account = $this->loyaltyService->getAccount($userId);

            return response()->json([
                'status' => 'success',
                'data' => $account,
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the request',
            ], 500);
        }
    }
}


