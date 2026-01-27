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
        'customer_id',
        'tier_id',
        'total_points',
        'available_points',
        'total_spent',
        'joined_at',
        'tier_updated_at',
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
        return $this->belongsTo(Customer::class);
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
        return $this->hasMany(PointTransaction::class, 'customer_id', 'customer_id');
    }

    /**
     * Add points
     */
    public function addPoints(int $points, string $type, ?string $reference = null): PointTransaction
    {
        $transaction = PointTransaction::create([
            'customer_id' => $this->customer_id,
            'type' => $type,
            'points' => $points,
            'balance_after' => $this->available_points + $points,
            'reference_type' => $reference ? 'order' : null,
            'reference_id' => null,
            'description' => $reference,
        ]);

        $this->total_points += $points;
        $this->available_points += $points;
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
