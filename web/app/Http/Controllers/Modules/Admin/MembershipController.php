<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Modules\Admin\MembershipTierStoreRequest;
use App\Http\Requests\Modules\Admin\MembershipTierUpdateRequest;
use App\Http\Requests\Modules\Admin\MembershipRedeemRequest;
use App\Services\Admin\MembershipService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class MembershipController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private MembershipService $membershipService
    ) {
    }

    public function tiers(): JsonResponse
    {
        try {
            $tiers = $this->membershipService->getTiers();
            return $this->successResponse($tiers);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function createTier(MembershipTierStoreRequest $request): JsonResponse
    {
        try {
            $tier = $this->membershipService->createTier($request->validated());
            return $this->createdResponse($tier, 'tier_created');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function updateTier(int $id, MembershipTierUpdateRequest $request): JsonResponse
    {
        try {
            $tier = $this->membershipService->updateTier($id, $request->validated());
            return $this->updatedResponse($tier, 'tier_updated');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function deleteTier(int $id): JsonResponse
    {
        try {
            $this->membershipService->deleteTier($id);
            return $this->deletedResponse('tier_deleted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function customerMembership(int $customerId): JsonResponse
    {
        try {
            $membership = $this->membershipService->getCustomerMembership($customerId);
            return $this->successResponse($membership);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function transactions(Request $request, int $customerId): JsonResponse
    {
        try {
            $transactions = $this->membershipService->getTransactions($customerId, $request->input('per_page', 20));
            return $this->paginatedResponse($transactions);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    public function redeem(int $customerId, MembershipRedeemRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $transaction = $this->membershipService->redeemPoints(
                $customerId,
                $validated['points'],
                $validated['reason'] ?? 'Đổi điểm'
            );

            return $this->successResponse($transaction);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    public function calculateDiscount(Request $request): JsonResponse
    {
        try {
            $customerId = $request->input('customer_id');
            $orderTotal = $request->input('order_total', 0);

            $discount = $this->membershipService->calculateDiscount($customerId, $orderTotal);
            return $this->successResponse($discount);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }
}



