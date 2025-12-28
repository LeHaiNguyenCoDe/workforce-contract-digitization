<?php

namespace App\Http\Controllers;

use App\Http\Requests\Store\MembershipTierStoreRequest;
use App\Http\Requests\Store\MembershipTierUpdateRequest;
use App\Http\Requests\Store\MembershipRedeemRequest;
use App\Services\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class MembershipController extends Controller
{
    public function __construct(
        private MembershipService $membershipService
    ) {
    }

    /**
     * Get all tiers
     */
    public function tiers(): JsonResponse
    {
        try {
            $tiers = $this->membershipService->getTiers();
            return response()->json(['status' => 'success', 'data' => $tiers]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Create tier
     */
    public function createTier(MembershipTierStoreRequest $request): JsonResponse
    {
        try {
            $tier = $this->membershipService->createTier($request->validated());
            return response()->json(['status' => 'success', 'data' => $tier], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update tier
     */
    public function updateTier(int $id, MembershipTierUpdateRequest $request): JsonResponse
    {
        try {
            $tier = $this->membershipService->updateTier($id, $request->validated());
            return response()->json(['status' => 'success', 'data' => $tier]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete tier
     */
    public function deleteTier(int $id): JsonResponse
    {
        try {
            $this->membershipService->deleteTier($id);
            return response()->json(['status' => 'success', 'message' => 'Đã xóa']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get customer membership
     */
    public function customerMembership(int $customerId): JsonResponse
    {
        try {
            $membership = $this->membershipService->getCustomerMembership($customerId);
            return response()->json(['status' => 'success', 'data' => $membership]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get point transactions
     */
    public function transactions(Request $request, int $customerId): JsonResponse
    {
        try {
            $transactions = $this->membershipService->getTransactions($customerId, $request->input('per_page', 20));
            return response()->json([
                'status' => 'success',
                'data' => $transactions->items(),
                'meta' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'total' => $transactions->total(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Redeem points
     */
    public function redeem(int $customerId, MembershipRedeemRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $transaction = $this->membershipService->redeemPoints(
                $customerId,
                $validated['points'],
                $validated['reason'] ?? 'Đổi điểm'
            );

            return response()->json(['status' => 'success', 'data' => $transaction]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Calculate discount for order
     */
    public function calculateDiscount(Request $request): JsonResponse
    {
        try {
            $customerId = $request->input('customer_id');
            $orderTotal = $request->input('order_total', 0);

            $discount = $this->membershipService->calculateDiscount($customerId, $orderTotal);
            return response()->json(['status' => 'success', 'data' => $discount]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
