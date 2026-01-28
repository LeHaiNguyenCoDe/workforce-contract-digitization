<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingKpi extends Model
{
    use HasFactory;

    const CHANNEL_EMAIL = 'email';
    const CHANNEL_SMS = 'sms';
    const CHANNEL_PUSH = 'push';
    const CHANNEL_SOCIAL = 'social';
    const CHANNEL_ALL = 'all';

    protected $fillable = [
        'date',
        'channel',
        'campaign_id',
        'total_sends',
        'total_deliveries',
        'total_bounces',
        'total_opens',
        'total_clicks',
        'total_unsubscribes',
        'total_conversions',
        'total_revenue',
        'total_cost',
        'open_rate',
        'click_rate',
        'conversion_rate',
        'roi',
        'calculated_at',
    ];

    protected $casts = [
        'date' => 'date',
        'total_sends' => 'integer',
        'total_deliveries' => 'integer',
        'total_bounces' => 'integer',
        'total_opens' => 'integer',
        'total_clicks' => 'integer',
        'total_unsubscribes' => 'integer',
        'total_conversions' => 'integer',
        'total_revenue' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'open_rate' => 'decimal:2',
        'click_rate' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'roi' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    /**
     * Campaign
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    /**
     * Calculate rates
     */
    public function calculateRates(): void
    {
        if ($this->total_deliveries > 0) {
            $this->open_rate = round(($this->total_opens / $this->total_deliveries) * 100, 2);
            $this->click_rate = round(($this->total_clicks / $this->total_deliveries) * 100, 2);
            $this->conversion_rate = round(($this->total_conversions / $this->total_deliveries) * 100, 2);
        }

        if ($this->total_cost > 0) {
            $this->roi = round((($this->total_revenue - $this->total_cost) / $this->total_cost) * 100, 2);
        }

        $this->calculated_at = now();
        $this->save();
    }

    /**
     * Get delivery rate
     */
    public function getDeliveryRate(): float
    {
        if ($this->total_sends == 0) {
            return 0;
        }

        return round(($this->total_deliveries / $this->total_sends) * 100, 2);
    }

    /**
     * Get bounce rate
     */
    public function getBounceRate(): float
    {
        if ($this->total_sends == 0) {
            return 0;
        }

        return round(($this->total_bounces / $this->total_sends) * 100, 2);
    }

    /**
     * Scope: by channel
     */
    public function scopeChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope: date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
