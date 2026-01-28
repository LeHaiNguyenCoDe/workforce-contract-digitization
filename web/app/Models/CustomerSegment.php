<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'conditions',
        'customer_count',
        'color',
        'icon',
        'created_by',
        'is_active',
        'last_calculated_at',
    ];

    protected $casts = [
        'conditions' => 'array',
        'customer_count' => 'integer',
        'is_active' => 'boolean',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Customers in this segment
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'segment_customers', 'segment_id', 'user_id')
            ->withPivot('source', 'added_at');
    }

    /**
     * History records
     */
    public function histories(): HasMany
    {
        return $this->hasMany(SegmentHistory::class, 'segment_id');
    }

    /**
     * Check if segment is dynamic
     */
    public function isDynamic(): bool
    {
        return $this->type === 'dynamic';
    }

    /**
     * Check if segment is static
     */
    public function isStatic(): bool
    {
        return $this->type === 'static';
    }

    /**
     * Get customers matching dynamic conditions
     */
    public function calculateDynamicCustomers(): \Illuminate\Database\Eloquent\Collection
    {
        if (!$this->isDynamic() || empty($this->conditions)) {
            return collect([]);
        }

        return $this->buildQueryFromConditions()->get();
    }

    /**
     * Build query from conditions
     */
    protected function buildQueryFromConditions()
    {
        $query = User::query();
        $conditions = $this->conditions;

        // Age range
        if (isset($conditions['age_min']) || isset($conditions['age_max'])) {
            if (isset($conditions['age_min'])) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) >= ?', [$conditions['age_min']]);
            }
            if (isset($conditions['age_max'])) {
                $query->whereRaw('TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) <= ?', [$conditions['age_max']]);
            }
        }

        // Gender
        if (isset($conditions['gender'])) {
            $query->where('gender', $conditions['gender']);
        }

        // Location
        if (isset($conditions['province'])) {
            $query->where('province', $conditions['province']);
        }

        // Total spent range
        if (isset($conditions['total_spent_min']) || isset($conditions['total_spent_max'])) {
            $query->whereHas('membership', function ($q) use ($conditions) {
                if (isset($conditions['total_spent_min'])) {
                    $q->where('total_spent', '>=', $conditions['total_spent_min']);
                }
                if (isset($conditions['total_spent_max'])) {
                    $q->where('total_spent', '<=', $conditions['total_spent_max']);
                }
            });
        }

        // Membership tier
        if (isset($conditions['tier_ids'])) {
            $query->whereHas('membership', function ($q) use ($conditions) {
                $q->whereIn('tier_id', $conditions['tier_ids']);
            });
        }

        // Points range
        if (isset($conditions['points_min']) || isset($conditions['points_max'])) {
            $query->whereHas('points', function ($q) use ($conditions) {
                if (isset($conditions['points_min'])) {
                    $q->where('available_points', '>=', $conditions['points_min']);
                }
                if (isset($conditions['points_max'])) {
                    $q->where('available_points', '<=', $conditions['points_max']);
                }
            });
        }

        // Order count range
        if (isset($conditions['order_count_min']) || isset($conditions['order_count_max'])) {
            $query->whereHas('membership', function ($q) use ($conditions) {
                if (isset($conditions['order_count_min'])) {
                    $q->where('total_orders', '>=', $conditions['order_count_min']);
                }
                if (isset($conditions['order_count_max'])) {
                    $q->where('total_orders', '<=', $conditions['order_count_max']);
                }
            });
        }

        // Last order date range (days ago)
        if (isset($conditions['last_order_days_min']) || isset($conditions['last_order_days_max'])) {
            $query->whereHas('orders', function ($q) use ($conditions) {
                if (isset($conditions['last_order_days_min'])) {
                    $q->where('created_at', '<=', now()->subDays($conditions['last_order_days_min']));
                }
                if (isset($conditions['last_order_days_max'])) {
                    $q->where('created_at', '>=', now()->subDays($conditions['last_order_days_max']));
                }
            });
        }

        // Birthday this month
        if (isset($conditions['birthday_this_month']) && $conditions['birthday_this_month']) {
            $query->whereMonth('birth_date', now()->month);
        }

        return $query;
    }
}
