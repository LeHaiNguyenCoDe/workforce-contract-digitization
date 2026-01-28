<?php

namespace App\Services\Admin;

use App\Models\MembershipTier;
use App\Models\CustomerMembership;
use App\Models\PointTransaction;
use App\Models\CustomerPoint;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    /**
     * Get all tiers
     */
    public function getTiers(): array
    {
        return MembershipTier::orderBy('sort_order')->get()->toArray();
    }

    /**
     * Create tier
     */
    public function createTier(array $data): MembershipTier
    {
        return MembershipTier::create($data);
    }

    /**
     * Update tier
     */
    public function updateTier(int $id, array $data): MembershipTier
    {
        $tier = MembershipTier::findOrFail($id);
        $tier->update($data);
        return $tier;
    }

    /**
     * Delete tier
     */
    public function deleteTier(int $id): bool
    {
        $tier = MembershipTier::findOrFail($id);
        
        if ($tier->customerMemberships()->count() > 0) {
            throw new \Exception('Không thể xóa hạng có khách hàng');
        }

        return $tier->delete();
    }

    /**
     * Get customer membership
     */
    public function getCustomerMembership(int $userId): ?CustomerMembership
    {
        return CustomerMembership::with(['tier', 'customer'])
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get customer points
     */
    public function getCustomerPoints(int $userId): CustomerPoint
    {
        return CustomerPoint::firstOrCreate(
            ['user_id' => $userId],
            ['available_points' => 0, 'total_earned' => 0]
        );
    }

    /**
     * Initialize membership for customer
     */
    public function initMembership(int $userId): CustomerMembership
    {
        $existing = CustomerMembership::where('user_id', $userId)->first();
        if ($existing) {
            return $existing;
        }

        $defaultTier = MembershipTier::where('is_active', true)
            ->orderBy('min_points')
            ->first();

        return CustomerMembership::create([
            'user_id' => $userId,
            'tier_id' => $defaultTier?->id,
            'total_spent' => 0,
            'total_orders' => 0,
            'tier_achieved_at' => now(),
        ]);
    }

    /**
     * Add points from order
     */
    public function earnPointsFromOrder(int $userId, float $orderTotal, int $orderId): ?PointTransaction
    {
        $membership = $this->getOrCreateMembership($userId);
        $pointsData = $this->getCustomerPoints($userId);
        
        // Calculate points (1000 VND = 1 point, with tier multiplier)
        $basePoints = floor($orderTotal / 1000);
        $multiplier = $membership->tier?->point_multiplier ?? 1;
        $earnedPoints = (int)($basePoints * $multiplier);

        if ($earnedPoints <= 0) {
            return null;
        }

        return DB::transaction(function () use ($membership, $pointsData, $earnedPoints, $orderTotal, $orderId, $userId) {
            // Update total spent in membership
            $membership->total_spent += $orderTotal;
            $membership->total_orders += 1;
            $membership->save();

            // Update points
            $pointsData->available_points += $earnedPoints;
            $pointsData->total_earned += $earnedPoints;
            $pointsData->save();

            // Create transaction
            $transaction = PointTransaction::create([
                'user_id' => $userId,
                'type' => PointTransaction::TYPE_EARN,
                'points' => $earnedPoints,
                'balance_after' => $pointsData->available_points,
                'reference_type' => 'order',
                'reference_id' => $orderId,
                'description' => "Đơn hàng #{$orderId}",
            ]);

            // Check tier upgrade
            $membership->checkTierUpgrade();

            return $transaction;
        });
    }

    /**
     * Redeem points
     */
    public function redeemPoints(int $userId, int $points, string $reason): PointTransaction
    {
        $pointsData = CustomerPoint::where('user_id', $userId)->firstOrFail();
        
        if ($points > $pointsData->available_points) {
            throw new \Exception('Không đủ điểm');
        }

        return DB::transaction(function () use ($pointsData, $points, $reason, $userId) {
            $pointsData->available_points -= $points;
            $pointsData->used_points += $points;
            $pointsData->save();

            return PointTransaction::create([
                'user_id' => $userId,
                'type' => PointTransaction::TYPE_REDEEM,
                'points' => -$points,
                'balance_after' => $pointsData->available_points,
                'description' => $reason,
            ]);
        });
    }

    /**
     * Get point transactions
     */
    public function getTransactions(int $userId, int $perPage = 20)
    {
        return PointTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get or create membership
     */
    protected function getOrCreateMembership(int $userId): CustomerMembership
    {
        $membership = CustomerMembership::where('user_id', $userId)->first();
        
        if (!$membership) {
            $membership = $this->initMembership($userId);
        }

        return $membership;
    }

    /**
     * Calculate discount for order
     */
    public function calculateDiscount(int $userId, float $orderTotal): array
    {
        $membership = $this->getCustomerMembership($userId);

        if (!$membership || !$membership->tier) {
            return ['discount_percent' => 0, 'discount_amount' => 0];
        }

        $discountPercent = $membership->tier->discount_percent;
        $discountAmount = $orderTotal * ($discountPercent / 100);

        return [
            'discount_percent' => $discountPercent,
            'discount_amount' => round($discountAmount, 0),
            'tier_name' => $membership->tier->name,
        ];
    }
}
