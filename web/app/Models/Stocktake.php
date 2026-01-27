<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stocktake extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'stocktake_code',
        'warehouse_id',
        'status',
        'started_at',
        'completed_at',
        'is_locked',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    // Statuses
    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get items
     */
    public function items(): HasMany
    {
        return $this->hasMany(StocktakeItem::class);
    }

    /**
     * Get creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Generate unique stocktake code
     */
    public static function generateCode(): string
    {
        $prefix = 'STK';
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}{$date}{$random}";
    }

    /**
     * Start stocktake
     */
    public function start(): void
    {
        $this->status = self::STATUS_IN_PROGRESS;
        $this->started_at = now();
        $this->is_locked = true;
        $this->save();
    }

    /**
     * Complete stocktake (submit for approval)
     */
    public function complete(): void
    {
        $this->status = self::STATUS_PENDING_APPROVAL;
        $this->completed_at = now();
        $this->save();
    }

    /**
     * Approve stocktake
     */
    public function approve(int $approverId): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->approved_by = $approverId;
        $this->approved_at = now();
        $this->is_locked = false;
        $this->save();
    }

    /**
     * Cancel stocktake
     */
    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
        $this->is_locked = false;
        $this->save();
    }

    /**
     * Get total difference
     */
    public function getTotalDifferenceAttribute(): int
    {
        return $this->items->sum('difference') ?? 0;
    }

    /**
     * Get items with discrepancy
     */
    public function getDiscrepancyItemsAttribute()
    {
        return $this->items->where('difference', '!=', 0);
    }
}
