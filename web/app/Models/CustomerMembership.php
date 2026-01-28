<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tier_id',
        'total_spent',
        'total_orders',
        'tier_achieved_at',
        'tier_expires_at',
    ];

    protected $casts = [
        'total_points' => 'integer',
        'available_points' => 'integer',
        'total_spent' => 'decimal:2',
        'joined_at' => 'datetime',
        'tier_updated_at' => 'datetime',
    ];

    /**
     * Get customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get tier
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class, 'tier_id');
    }

    /**
     * Get point transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'user_id', 'user_id');
    }

    /**
     * Add points
     */
    public function addPoints(int $points, string $type, ?string $reference = null): PointTransaction
    {
        $transaction = PointTransaction::create([
            'user_id' => $this->user_id,
            'type' => $type,
            'points' => $points,
            'balance_after' => $this->getAvailablePoints() + $points, // This logic needs adjustment because points are in customer_points table
            'reference_type' => $reference ? 'order' : null,
            'reference_id' => null,
            'description' => $reference,
        ]);

        $this->total_spent += 1; // dummy for now or use real spend logic
        $this->save();

        // Check tier upgrade
        $this->checkTierUpgrade();

        return $transaction;
    }

    /**
     * Use points
     */
    public function usePoints(int $points, ?string $reference = null): PointTransaction
    {
        if ($points > $this->available_points) {
            throw new \Exception('Không đủ điểm');
        }

        $transaction = PointTransaction::create([
            'customer_id' => $this->customer_id,
            'type' => 'redeem',
            'points' => -$points,
            'balance_after' => $this->available_points - $points,
            'description' => $reference,
        ]);

        $this->available_points -= $points;
        $this->save();

        return $transaction;
    }

    /**
     * Check and upgrade tier
     */
    public function checkTierUpgrade(): void
    {
        $newTier = MembershipTier::getTierForPoints($this->total_points);
        
        if ($newTier && (!$this->tier_id || $newTier->id !== $this->tier_id)) {
            $this->tier_id = $newTier->id;
            $this->tier_updated_at = now();
            $this->save();
        }
    }
}
