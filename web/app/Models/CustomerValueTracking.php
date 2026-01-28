<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerValueTracking extends Model
{
    use HasFactory;

    protected $table = 'customer_value_tracking';

    protected $fillable = [
        'user_id',
        'first_order_date',
        'last_order_date',
        'total_orders',
        'total_spent',
        'average_order_value',
        'days_since_first_order',
        'days_since_last_order',
        'purchase_frequency',
        'clv_actual',
        'clv_predicted',
        'is_churned',
        'churn_probability',
        'acquisition_source',
        'acquisition_cost',
        'last_calculated_at',
    ];

    protected $casts = [
        'first_order_date' => 'date',
        'last_order_date' => 'date',
        'total_orders' => 'integer',
        'total_spent' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'days_since_first_order' => 'integer',
        'days_since_last_order' => 'integer',
        'purchase_frequency' => 'decimal:2',
        'clv_actual' => 'decimal:2',
        'clv_predicted' => 'decimal:2',
        'is_churned' => 'boolean',
        'churn_probability' => 'decimal:2',
        'acquisition_cost' => 'decimal:2',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate metrics from user orders
     */
    public function calculate(): void
    {
        $user = $this->user;
        $orders = $user->orders()
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at')
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        // Basic metrics
        $this->first_order_date = $orders->first()->created_at->toDateString();
        $this->last_order_date = $orders->last()->created_at->toDateString();
        $this->total_orders = $orders->count();
        $this->total_spent = $orders->sum('total_amount');
        $this->average_order_value = $this->total_spent / $this->total_orders;

        // Time-based metrics
        $this->days_since_first_order = now()->diffInDays($this->first_order_date);
        $this->days_since_last_order = now()->diffInDays($this->last_order_date);

        // Purchase frequency (orders per month)
        if ($this->days_since_first_order > 0) {
            $this->purchase_frequency = ($this->total_orders / $this->days_since_first_order) * 30;
        }

        // CLV actual
        $this->clv_actual = $this->total_spent;

        // CLV predicted (simplified formula: AOV * Purchase Frequency * Average Customer Lifespan)
        $avgLifespanMonths = 24; // Assume 2 years
        $this->clv_predicted = $this->average_order_value * $this->purchase_frequency * $avgLifespanMonths;

        // Churn detection (if no order in 90 days)
        $churnThresholdDays = 90;
        $this->is_churned = $this->days_since_last_order > $churnThresholdDays;

        // Churn probability (simplified)
        if ($this->days_since_last_order < 30) {
            $this->churn_probability = 10;
        } elseif ($this->days_since_last_order < 60) {
            $this->churn_probability = 30;
        } elseif ($this->days_since_last_order < 90) {
            $this->churn_probability = 60;
        } else {
            $this->churn_probability = 90;
        }

        $this->last_calculated_at = now();
        $this->save();
    }

    /**
     * Get ROI
     */
    public function getRoi(): float
    {
        if ($this->acquisition_cost == 0) {
            return 0;
        }

        return round((($this->clv_actual - $this->acquisition_cost) / $this->acquisition_cost) * 100, 2);
    }

    /**
     * Scope: high value customers
     */
    public function scopeHighValue($query, float $threshold = 1000000)
    {
        return $query->where('clv_predicted', '>=', $threshold);
    }

    /**
     * Scope: churned customers
     */
    public function scopeChurned($query)
    {
        return $query->where('is_churned', true);
    }

    /**
     * Scope: at risk (high churn probability)
     */
    public function scopeAtRisk($query, float $threshold = 60)
    {
        return $query->where('is_churned', false)
            ->where('churn_probability', '>=', $threshold);
    }
}
