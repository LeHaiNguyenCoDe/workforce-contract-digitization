<?php

namespace App\Http\Controllers;

use App\Services\MembershipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class MembershipController extends Controller
{
    public function __construct(
        private MembershipService $membershipService
    ) {}

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
    public function createTier(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'code' => 'required|string|max:50|unique:membership_tiers,code',
                'min_points' => 'required|integer|min:0',
                'max_points' => 'nullable|integer|min:0',
                'discount_percent' => 'nullable|numeric|min:0|max:100',
                'point_multiplier' => 'nullable|numeric|min:1',
                'benefits' => 'nullable|array',
                'color' => 'nullable|string|max:20',
            ]);

            $tier = $this->membershipService->createTier($validated);
            return response()->json(['status' => 'success', 'data' => $tier], 201);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update tier
     */
    public function updateTier(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:100',
                'min_points' => 'nullable|integer|min:0',
                'max_points' => 'nullable|integer|min:0',
                'discount_percent' => 'nullable|numeric|min:0|max:100',
                'point_multiplier' => 'nullable|numeric|min:1',
                'benefits' => 'nullable|array',
                'color' => 'nullable|string|max:20',
                'is_active' => 'nullable|boolean',
            ]);

            $tier = $this->membershipService->updateTier($id, $validated);
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
    public function redeem(Request $request, int $customerId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'points' => 'required|integer|min:1',
                'reason' => 'nullable|string',
            ]);

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
