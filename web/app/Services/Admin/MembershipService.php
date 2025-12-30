<?php

namespace App\Services\Admin;

use App\Models\MembershipTier;
use App\Models\CustomerMembership;
use App\Models\PointTransaction;
use App\Models\Customer;
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
    public function getCustomerMembership(int $customerId): ?CustomerMembership
    {
        return CustomerMembership::with('tier')
            ->where('customer_id', $customerId)
            ->first();
    }

    /**
     * Initialize membership for customer
     */
    public function initMembership(int $customerId): CustomerMembership
    {
        $existing = CustomerMembership::where('customer_id', $customerId)->first();
        if ($existing) {
            return $existing;
        }

        $defaultTier = MembershipTier::where('is_active', true)
            ->orderBy('min_points')
            ->first();

        return CustomerMembership::create([
            'customer_id' => $customerId,
            'tier_id' => $defaultTier?->id,
            'total_points' => 0,
            'available_points' => 0,
            'total_spent' => 0,
            'joined_at' => now(),
        ]);
    }

    /**
     * Add points from order
     */
    public function earnPointsFromOrder(int $customerId, float $orderTotal, int $orderId): ?PointTransaction
    {
        $membership = $this->getOrCreateMembership($customerId);
        
        // Calculate points (1000 VND = 1 point, with tier multiplier)
        $basePoints = floor($orderTotal / 1000);
        $multiplier = $membership->tier?->point_multiplier ?? 1;
        $earnedPoints = (int)($basePoints * $multiplier);

        if ($earnedPoints <= 0) {
            return null;
        }

        // Update total spent
        $membership->total_spent += $orderTotal;
        $membership->save();

        return $membership->addPoints($earnedPoints, PointTransaction::TYPE_EARN, "Đơn hàng #{$orderId}");
    }

    /**
     * Redeem points
     */
    public function redeemPoints(int $customerId, int $points, string $reason): PointTransaction
    {
        $membership = CustomerMembership::where('customer_id', $customerId)->firstOrFail();
        return $membership->usePoints($points, $reason);
    }

    /**
     * Get point transactions
     */
    public function getTransactions(int $customerId, int $perPage = 20)
    {
        return PointTransaction::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get or create membership
     */
    protected function getOrCreateMembership(int $customerId): CustomerMembership
    {
        $membership = CustomerMembership::where('customer_id', $customerId)->first();
        
        if (!$membership) {
            $membership = $this->initMembership($customerId);
        }

        return $membership;
    }

    /**
     * Calculate discount for order
     */
    public function calculateDiscount(int $customerId, float $orderTotal): array
    {
        $membership = $this->getCustomerMembership($customerId);

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
