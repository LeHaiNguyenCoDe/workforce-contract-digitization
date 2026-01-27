<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'min_points',
        'max_points',
        'discount_percent',
        'point_multiplier',
        'benefits',
        'color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_points' => 'integer',
        'max_points' => 'integer',
        'discount_percent' => 'decimal:2',
        'point_multiplier' => 'decimal:2',
        'benefits' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get customers in this tier
     */
    public function customerMemberships(): HasMany
    {
        return $this->hasMany(CustomerMembership::class, 'tier_id');
    }

    /**
     * Get tier for points
     */
    public static function getTierForPoints(int $points): ?self
    {
        return static::where('is_active', true)
            ->where('min_points', '<=', $points)
            ->where(function ($q) use ($points) {
                $q->whereNull('max_points')
                  ->orWhere('max_points', '>=', $points);
            })
            ->orderBy('min_points', 'desc')
            ->first();
    }
}
