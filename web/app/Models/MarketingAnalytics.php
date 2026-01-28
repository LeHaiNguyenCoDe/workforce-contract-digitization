<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingAnalytics extends Model
{
    use HasFactory;

    protected $table = 'marketing_analytics';

    protected $fillable = [
        'metric_type',
        'metric_value',
        'metric_date',
        'segment_id',
        'campaign_id',
        'user_id',
        'metadata',
    ];

    protected $casts = [
        'metric_value' => 'decimal:2',
        'metric_date' => 'date',
        'metadata' => 'array',
    ];

    const METRIC_LEAD_CREATED = 'lead_created';
    const METRIC_LEAD_CONVERTED = 'lead_converted';
    const METRIC_COUPON_USED = 'coupon_used';
    const METRIC_COUPON_DISCOUNT = 'coupon_discount';
    const METRIC_REVENUE = 'revenue';
    const METRIC_SEGMENT_SIZE = 'segment_size';
    const METRIC_CUSTOMER_VALUE = 'customer_value';
    const METRIC_CHURN_RATE = 'churn_rate';
    const METRIC_RETENTION_RATE = 'retention_rate';
    const METRIC_AOV = 'average_order_value';
    const METRIC_LTV = 'lifetime_value';
    const METRIC_CAC = 'customer_acquisition_cost';

    /**
     * Get all metrics for a date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('metric_date', [$startDate, $endDate]);
    }

    /**
     * Get metrics by type
     */
    public function scopeByMetricType($query, $type)
    {
        return $query->where('metric_type', $type);
    }

    /**
     * Get segment metrics
     */
    public function scopeBySegment($query, $segmentId)
    {
        return $query->where('segment_id', $segmentId);
    }
}
