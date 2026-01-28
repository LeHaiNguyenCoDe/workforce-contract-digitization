<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED = 'fixed';
    const TYPE_BXGY = 'bxgy';
    const TYPE_FREE_SHIPPING = 'free_shipping';

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'min_purchase_amount',
        'max_discount_amount',
        'usage_limit_total',
        'usage_limit_per_user',
        'usage_limit_per_day',
        'usage_count',
        'applicable_products',
        'applicable_categories',
        'applicable_segments',
        'excluded_products',
        'bxgy_buy_quantity',
        'bxgy_get_quantity',
        'bxgy_get_products',
        'stackable',
        'auto_apply',
        'first_order_only',
        'valid_from',
        'valid_to',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit_total' => 'integer',
        'usage_limit_per_user' => 'integer',
        'usage_limit_per_day' => 'integer',
        'usage_count' => 'integer',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'applicable_segments' => 'array',
        'excluded_products' => 'array',
        'bxgy_buy_quantity' => 'integer',
        'bxgy_get_quantity' => 'integer',
        'bxgy_get_products' => 'array',
        'stackable' => 'boolean',
        'auto_apply' => 'boolean',
        'first_order_only' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usages
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Check if coupon is valid
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_to && $now->gt($this->valid_to)) {
            return false;
        }

        if ($this->usage_limit_total && $this->usage_count >= $this->usage_limit_total) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can use this coupon
     */
    public function canBeUsedBy(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check user segment
        if (!empty($this->applicable_segments)) {
            $userSegments = $user->segments()->pluck('customer_segments.id')->toArray();
            if (empty(array_intersect($this->applicable_segments, $userSegments))) {
                return false;
            }
        }

        // Check first order only
        if ($this->first_order_only) {
            $hasOrders = $user->orders()->where('status', '!=', 'cancelled')->exists();
            if ($hasOrders) {
                return false;
            }
        }

        // Check per-user limit
        if ($this->usage_limit_per_user) {
            $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
            if ($userUsageCount >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check daily usage limit
     */
    public function hasReachedDailyLimit(): bool
    {
        if (!$this->usage_limit_per_day) {
            return false;
        }

        $todayUsage = $this->usages()
            ->whereDate('used_at', today())
            ->count();

        return $todayUsage >= $this->usage_limit_per_day;
    }

    /**
     * Calculate discount for order
     */
    public function calculateDiscount(float $orderAmount, array $orderItems = []): float
    {
        if ($orderAmount < $this->min_purchase_amount) {
            return 0;
        }

        $discount = 0;

        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                $discount = $orderAmount * ($this->value / 100);
                if ($this->max_discount_amount) {
                    $discount = min($discount, $this->max_discount_amount);
                }
                break;

            case self::TYPE_FIXED:
                $discount = $this->value;
                break;

            case self::TYPE_BXGY:
                // Simplified BXGY - would need more complex logic with actual items
                $discount = 0;
                break;

            case self::TYPE_FREE_SHIPPING:
                // Return shipping cost (would need to be passed in)
                $discount = $this->value;
                break;
        }

        return round($discount, 2);
    }

    /**
     * Increment usage count
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Scope: active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_to')
                  ->orWhere('valid_to', '>=', now());
            });
    }

    /**
     * Scope: auto-apply coupons
     */
    public function scopeAutoApply($query)
    {
        return $query->where('auto_apply', true);
    }
}
