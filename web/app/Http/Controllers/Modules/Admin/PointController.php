<?php

namespace App\Http\Controllers\Modules\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\MembershipService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class PointController extends Controller
{
    use StoreApiResponse;

    public function __construct(
        private MembershipService $membershipService
    ) {
    }

    /**
     * Get customer points info
     */
    public function show(int $customerId): JsonResponse
    {
        try {
            $membership = $this->membershipService->getCustomerMembership($customerId);
            if (!$membership) {
                return $this->notFoundResponse('customer_membership_not_found');
            }

            // Map fields to match FE expectation if necessary
            // FE expects: current_points, total_earned, tier_name, tier_color, etc.
            
            // Get points from customer_points table
            $points = \App\Models\CustomerPoint::where('user_id', $customerId)->first();
            
            $data = [
                'customer_id' => $membership->user_id,
                'customer_name' => $membership->user?->full_name ?? $membership->user?->name ?? 'N/A',
                'customer_email' => $membership->user?->email,
                'current_points' => $points?->available_points ?? 0,
                'total_earned' => $points?->total_earned ?? 0,
                'total_redeemed' => $points?->used_points ?? 0,
                'tier_name' => $membership->tier?->name ?? 'None',
                'tier_color' => $membership->tier?->color ?? '#64748b',
                'total_spent' => $membership->total_spent ?? 0,
            ];

            return $this->successResponse($data);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Get transactions
     */
    public function transactions(Request $request, int $customerId): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $transactions = $this->membershipService->getTransactions($customerId, $perPage);
            return $this->paginatedResponse($transactions);
        } catch (Exception $e) {
            return $this->serverErrorResponse('error', $e);
        }
    }

    /**
     * Redeem points
     */
    public function redeem(Request $request, int $customerId): JsonResponse
    {
        try {
            $points = $request->input('amount');
            $reason = $request->input('description', 'Đã đổi quà');

            if (!$points || $points <= 0) {
                return $this->errorResponse('Số điểm không hợp lệ', null, 422);
            }

            $transaction = $this->membershipService->redeemPoints($customerId, $points, $reason);
            return $this->successResponse($transaction, 'points_redeemed');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }

    /**
     * Adjust points (optional, added for completeness based on pointsService.ts)
     */
    public function adjust(Request $request, int $customerId): JsonResponse
    {
        try {
            $amount = $request->input('amount');
            $reason = $request->input('description', 'Điều chỉnh hệ thống');

            $membership = \App\Models\CustomerMembership::where('user_id', $customerId)->firstOrFail();
            
            if ($amount > 0) {
                $transaction = $membership->addPoints($amount, \App\Models\PointTransaction::TYPE_ADJUST, $reason);
            } else {
                $transaction = $membership->usePoints(abs($amount), $reason);
            }

            return $this->successResponse($transaction, 'points_adjusted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), null, 422);
        }
    }
}
