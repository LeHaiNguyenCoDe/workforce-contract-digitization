<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    // BR-SALES-02: Order Status Flow
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'code',
        'full_name',
        'phone',
        'email',
        'address_line',
        'ward',
        'district',
        'province',
        'payment_method',
        'status',
        'total_amount',
        'cost_amount',      // BR-SALES-03: Giá vốn
        'paid_amount',      // BR-SALES-04: Đã thanh toán
        'remaining_amount', // BR-SALES-04: Còn lại
        'note',
        'confirmed_at',
        'delivered_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'cost_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function receivable(): HasOne
    {
        return $this->hasOne(AccountReceivable::class);
    }

    public function canConfirm(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    public function canComplete(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function getProfit(): float
    {
        return (float) $this->total_amount - (float) $this->cost_amount;
    }
}
