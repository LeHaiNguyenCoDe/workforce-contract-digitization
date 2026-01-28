<?php

namespace App\Services\Marketing;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\CouponGenerationBatch;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CouponService
{
    /**
     * Get all coupons
     */
    public function getAll(int $perPage = 20)
    {
        return Coupon::with('creator')
            ->withCount('usages')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create coupon
     */
    public function create(array $data): Coupon
    {
        return Coupon::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => $data['value'],
            'min_purchase_amount' => $data['min_purchase_amount'] ?? 0,
            'max_discount_amount' => $data['max_discount_amount'] ?? null,
            'usage_limit_total' => $data['usage_limit_total'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,
            'usage_limit_per_day' => $data['usage_limit_per_day'] ?? null,
            'applicable_products' => $data['applicable_products'] ?? null,
            'applicable_categories' => $data['applicable_categories'] ?? null,
            'applicable_segments' => $data['applicable_segments'] ?? null,
            'excluded_products' => $data['excluded_products'] ?? null,
            'bxgy_buy_quantity' => $data['bxgy_buy_quantity'] ?? null,
            'bxgy_get_quantity' => $data['bxgy_get_quantity'] ?? null,
            'bxgy_get_products' => $data['bxgy_get_products'] ?? null,
            'stackable' => $data['stackable'] ?? false,
            'auto_apply' => $data['auto_apply'] ?? false,
            'first_order_only' => $data['first_order_only'] ?? false,
            'valid_from' => $data['valid_from'] ?? null,
            'valid_to' => $data['valid_to'] ?? null,
            'created_by' => auth()->id(),
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update coupon
     */
    public function update(int $id, array $data): Coupon
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update($data);
        return $coupon->fresh('creator');
    }

    /**
     * Delete coupon
     */
    public function delete(int $id): bool
    {
        $coupon = Coupon::findOrFail($id);
        return $coupon->delete();
    }

    /**
     * Validate coupon code
     */
    public function validateCoupon(string $code, User $user, float $orderAmount): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon) {
            return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại'];
        }

        if (!$coupon->isValid()) {
            return ['valid' => false, 'message' => 'Mã giảm giá không còn hiệu lực'];
        }

        if (!$coupon->canBeUsedBy($user)) {
            return ['valid' => false, 'message' => 'Bạn không thể sử dụng mã này'];
        }

        if ($coupon->hasReachedDailyLimit()) {
            return ['valid' => false, 'message' => 'Mã đã đạt giới hạn sử dụng hôm nay'];
        }

        if ($orderAmount < $coupon->min_purchase_amount) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Đơn hàng tối thiểu %s để sử dụng mã này',
                    number_format($coupon->min_purchase_amount)
                )
            ];
        }

        $discountAmount = $coupon->calculateDiscount($orderAmount);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
        ];
    }

    /**
     * Apply coupon to order
     */
    public function applyCoupon(Coupon $coupon, User $user, Order $order): CouponUsage
    {
        return DB::transaction(function () use ($coupon, $user, $order) {
            // Create usage record
            $usage = CouponUsage::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'order_id' => $order->id,
                'order_amount' => $order->total_amount,
                'discount_amount' => $coupon->calculateDiscount($order->total_amount),
                'used_at' => now(),
            ]);

            // Increment usage count
            $coupon->incrementUsage();

            return $usage;
        });
    }

    /**
     * Get applicable coupons for user
     */
    public function getApplicableCoupons(User $user, float $orderAmount = 0)
    {
        return Coupon::active()
            ->where(function ($query) use ($orderAmount) {
                if ($orderAmount > 0) {
                    $query->where('min_purchase_amount', '<=', $orderAmount);
                }
            })
            ->get()
            ->filter(function ($coupon) use ($user) {
                return $coupon->canBeUsedBy($user);
            });
    }

    /**
     * Get auto-apply coupons for order
     */
    public function getAutoApplyCoupons(User $user, float $orderAmount): ?\Coupon
    {
        $coupons = Coupon::active()
            ->autoApply()
            ->where('min_purchase_amount', '<=', $orderAmount)
            ->get()
            ->filter(function ($coupon) use ($user) {
                return $coupon->canBeUsedBy($user);
            })
            ->sortByDesc(function ($coupon) use ($orderAmount) {
                return $coupon->calculateDiscount($orderAmount);
            });

        return $coupons->first();
    }

    /**
     * Generate bulk coupons
     */
    public function generateBulkCoupons(array $data): CouponGenerationBatch
    {
        $batch = CouponGenerationBatch::create([
            'name' => $data['name'],
            'prefix' => strtoupper($data['prefix']),
            'quantity' => $data['quantity'],
            'template' => $data['template'],
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);

        // Generate coupons in background (would use queue in production)
        for ($i = 0; $i < $data['quantity']; $i++) {
            $code = $this->generateUniqueCode($batch->prefix);

            Coupon::create(array_merge($data['template'], [
                'code' => $code,
                'name' => $batch->name . ' #' . ($i + 1),
                'created_by' => auth()->id(),
            ]));

            $batch->increment('generated_count');
        }

        return $batch->fresh();
    }

    /**
     * Generate unique coupon code
     */
    protected function generateUniqueCode(string $prefix): string
    {
        do {
            $code = $prefix . strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get coupon usage statistics
     */
    public function getCouponStats(int $couponId): array
    {
        $coupon = Coupon::with('usages')->findOrFail($couponId);

        return [
            'total_usages' => $coupon->usages->count(),
            'total_discount_given' => (float) $coupon->usages->sum('discount_amount'),
            'total_revenue' => (float) $coupon->usages->sum('order_amount'),
            'unique_users' => $coupon->usages->pluck('user_id')->unique()->count(),
            'usage_by_day' => $coupon->usages()
                ->selectRaw('DATE(used_at) as date, COUNT(*) as count, SUM(discount_amount) as discount')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get(),
        ];
    }

    /**
     * Get overview statistics for all coupons
     */
    public function getStats(): array
    {
        $totalCoupons = Coupon::count();
        $totalUsages = CouponUsage::count();
        $totalDiscount = CouponUsage::sum('discount_amount');
        
        $usageRate = $totalCoupons > 0 ? round(($totalUsages / $totalCoupons) * 100, 2) : 0;

        return [
            'total_coupons' => $totalCoupons,
            'total_usages' => $totalUsages,
            'usage_rate' => $usageRate,
            'total_discount_given' => (float) $totalDiscount,
        ];
    }

    /**
     * Generate codes for a specific coupon template
     */
    public function generateCodes(int $couponId, int $quantity, ?string $prefix = null): CouponGenerationBatch
    {
        $templateCoupon = Coupon::findOrFail($couponId);
        
        return DB::transaction(function () use ($templateCoupon, $quantity, $prefix) {
            $batch = CouponGenerationBatch::create([
                'name' => 'Batch for ' . $templateCoupon->code,
                'prefix' => strtoupper($prefix ?? $templateCoupon->code),
                'quantity' => $quantity,
                'template' => $templateCoupon->toArray(),
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]);

            for ($i = 0; $i < $quantity; $i++) {
                $code = $this->generateUniqueCode($batch->prefix);
                
                $newCouponData = $templateCoupon->toArray();
                // Remove ID and timestamps to create a new record
                unset($newCouponData['id'], $newCouponData['created_at'], $newCouponData['updated_at']);
                $newCouponData['code'] = $code;
                $newCouponData['usage_count'] = 0;
                $newCouponData['created_by'] = auth()->id();
                
                Coupon::create($newCouponData);
                $batch->increment('generated_count');
            }

            return $batch->fresh();
        });
    }

    /**
     * Export coupons based on filters
     */
    public function export(array $filters)
    {
        $query = Coupon::with('creator')->withCount('usages');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->active();
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->get();
    }
}
