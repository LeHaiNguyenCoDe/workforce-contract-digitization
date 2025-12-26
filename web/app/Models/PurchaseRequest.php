<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'request_code',
        'product_id',
        'warehouse_id',
        'supplier_id',
        'requested_quantity',
        'current_stock',
        'min_stock',
        'status',
        'source',
        'notes',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_quantity' => 'integer',
        'current_stock' => 'integer',
        'min_stock' => 'integer',
        'approved_at' => 'datetime',
    ];

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ORDERED = 'ordered';
    const STATUS_COMPLETED = 'completed';

    // Sources
    const SOURCE_AUTO = 'auto';
    const SOURCE_MANUAL = 'manual';

    /**
     * Get product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get requester
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for auto-generated requests
     */
    public function scopeAuto($query)
    {
        return $query->where('source', self::SOURCE_AUTO);
    }

    /**
     * Check if can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]);
    }

    /**
     * Generate unique request code
     */
    public static function generateRequestCode(): string
    {
        $prefix = 'PR';
        $date = now()->format('ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return sprintf('%s%s%04d', $prefix, $date, $count);
    }
}
