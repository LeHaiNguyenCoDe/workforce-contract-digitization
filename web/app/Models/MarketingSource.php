<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingSource extends Model
{
    use HasFactory;

    const TYPE_PAID = 'paid';
    const TYPE_ORGANIC = 'organic';
    const TYPE_REFERRAL = 'referral';
    const TYPE_DIRECT = 'direct';
    const TYPE_SOCIAL = 'social';
    const TYPE_EMAIL = 'email';

    protected $fillable = [
        'name',
        'type',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'total_sessions',
        'total_users',
        'total_conversions',
        'total_revenue',
        'cost_spent',
        'conversion_rate',
        'roi',
        'cpa',
        'last_updated_at',
    ];

    protected $casts = [
        'total_sessions' => 'integer',
        'total_users' => 'integer',
        'total_conversions' => 'integer',
        'total_revenue' => 'decimal:2',
        'cost_spent' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'roi' => 'decimal:2',
        'cpa' => 'decimal:2',
        'last_updated_at' => 'datetime',
    ];

    /**
     * Calculate metrics
     */
    public function calculateMetrics(): void
    {
        if ($this->total_sessions > 0) {
            $this->conversion_rate = round(($this->total_conversions / $this->total_sessions) * 100, 2);
        }

        if ($this->cost_spent > 0) {
            $this->roi = round((($this->total_revenue - $this->cost_spent) / $this->cost_spent) * 100, 2);
            $this->cpa = $this->total_conversions > 0
                ? round($this->cost_spent / $this->total_conversions, 2)
                : 0;
        }

        $this->last_updated_at = now();
        $this->save();
    }

    /**
     * Get ROAS (Return on Ad Spend)
     */
    public function getRoas(): float
    {
        if ($this->cost_spent == 0) {
            return 0;
        }

        return round($this->total_revenue / $this->cost_spent, 2);
    }

    /**
     * Get revenue per session
     */
    public function getRevenuePerSession(): float
    {
        if ($this->total_sessions == 0) {
            return 0;
        }

        return round($this->total_revenue / $this->total_sessions, 2);
    }

    /**
     * Scope: by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: profitable sources
     */
    public function scopeProfitable($query)
    {
        return $query->where('roi', '>', 0);
    }

    /**
     * Scope: high performing (conversion rate > threshold)
     */
    public function scopeHighPerforming($query, float $threshold = 5)
    {
        return $query->where('conversion_rate', '>=', $threshold);
    }
}
