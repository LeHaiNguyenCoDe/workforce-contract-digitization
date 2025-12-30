<?php

namespace App\Http\Controllers\Modules\Landing;

use App\Http\Controllers\Controller;
use App\Services\Landing\LoyaltyService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private LoyaltyService $loyaltyService
    ) {
    }

    public function show(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $account = $this->loyaltyService->getAccount($userId);

            return $this->successResponse($account);
        } catch (\Exception $ex) {
            return $this->serverErrorResponse('error', $ex);
        }
    }
}



