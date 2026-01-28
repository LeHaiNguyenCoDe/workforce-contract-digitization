<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributionTouchpoint extends Model
{
    use HasFactory;

    const POSITION_FIRST = 'first';
    const POSITION_MIDDLE = 'middle';
    const POSITION_LAST = 'last';

    const MODEL_LAST_CLICK = 'last_click';
    const MODEL_FIRST_CLICK = 'first_click';
    const MODEL_LINEAR = 'linear';
    const MODEL_TIME_DECAY = 'time_decay';
    const MODEL_POSITION_BASED = 'position_based';

    protected $fillable = [
        'user_id',
        'session_id',
        'order_id',
        'source',
        'medium',
        'campaign',
        'content',
        'term',
        'touchpoint_position',
        'position_order',
        'revenue_attributed',
        'attribution_model',
        'referrer_url',
        'landing_page',
        'device_type',
        'occurred_at',
    ];

    protected $casts = [
        'position_order' => 'integer',
        'revenue_attributed' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];

    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Calculate attribution based on model
     */
    public static function calculateAttribution(Order $order, string $model = self::MODEL_LAST_CLICK): void
    {
        $touchpoints = static::where('session_id', $order->session_id ?? '')
            ->orWhere('user_id', $order->user_id)
            ->orderBy('occurred_at')
            ->get();

        if ($touchpoints->isEmpty()) {
            return;
        }

        $revenue = (float) $order->total_amount;
        $count = $touchpoints->count();

        // Set positions
        $touchpoints->each(function ($tp, $index) use ($count) {
            $tp->position_order = $index + 1;
            if ($index == 0) {
                $tp->touchpoint_position = self::POSITION_FIRST;
            } elseif ($index == $count - 1) {
                $tp->touchpoint_position = self::POSITION_LAST;
            } else {
                $tp->touchpoint_position = self::POSITION_MIDDLE;
            }
            $tp->order_id = $tp->order_id ?? null;
        });

        // Calculate attribution
        switch ($model) {
            case self::MODEL_LAST_CLICK:
                $touchpoints->last()->revenue_attributed = $revenue;
                break;

            case self::MODEL_FIRST_CLICK:
                $touchpoints->first()->revenue_attributed = $revenue;
                break;

            case self::MODEL_LINEAR:
                $perTouchpoint = $revenue / $count;
                $touchpoints->each(function ($tp) use ($perTouchpoint) {
                    $tp->revenue_attributed = $perTouchpoint;
                });
                break;

            case self::MODEL_TIME_DECAY:
                // More recent touchpoints get more credit
                $totalWeight = 0;
                $weights = [];
                for ($i = 0; $i < $count; $i++) {
                    $weight = pow(2, $i); // Exponential weight
                    $weights[] = $weight;
                    $totalWeight += $weight;
                }
                $touchpoints->each(function ($tp, $index) use ($revenue, $weights, $totalWeight) {
                    $tp->revenue_attributed = ($weights[$index] / $totalWeight) * $revenue;
                });
                break;

            case self::MODEL_POSITION_BASED:
                // 40% first, 40% last, 20% divided among middle
                if ($count == 1) {
                    $touchpoints->first()->revenue_attributed = $revenue;
                } elseif ($count == 2) {
                    $touchpoints->first()->revenue_attributed = $revenue * 0.5;
                    $touchpoints->last()->revenue_attributed = $revenue * 0.5;
                } else {
                    $touchpoints->first()->revenue_attributed = $revenue * 0.4;
                    $touchpoints->last()->revenue_attributed = $revenue * 0.4;
                    $middleRevenue = $revenue * 0.2;
                    $perMiddle = $middleRevenue / ($count - 2);
                    $touchpoints->slice(1, $count - 2)->each(function ($tp) use ($perMiddle) {
                        $tp->revenue_attributed = $perMiddle;
                    });
                }
                break;
        }

        // Save all touchpoints
        $touchpoints->each(function ($tp) use ($model, $order) {
            $tp->attribution_model = $model;
            $tp->order_id = $order->id;
            $tp->save();
        });
    }

    /**
     * Scope: by source
     */
    public function scopeSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: by campaign
     */
    public function scopeCampaign($query, string $campaign)
    {
        return $query->where('campaign', $campaign);
    }
}
