<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AccountReceivable - Công nợ phải thu (BR-DEBT-01)
 */
class AccountReceivable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ar_code',
        'order_id',
        'customer_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    const STATUS_OPEN = 'open';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_WRITTEN_OFF = 'written_off';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'debt_id')
            ->where('debt_type', 'ar');
    }

    /**
     * Generate AR code
     */
    public static function generateCode(): string
    {
        $date = now()->format('ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return sprintf('AR%s%04d', $date, $count);
    }

    /**
     * Record payment
     */
    public function recordPayment(float $amount): void
    {
        $this->paid_amount += $amount;
        $this->remaining_amount = $this->total_amount - $this->paid_amount;

        if ($this->remaining_amount <= 0) {
            $this->status = self::STATUS_PAID;
        } elseif ($this->paid_amount > 0) {
            $this->status = self::STATUS_PARTIAL;
        }

        $this->save();
    }

    /**
     * Check overdue status
     */
    public function checkOverdue(): void
    {
        if ($this->due_date && $this->due_date < today() && $this->status !== self::STATUS_PAID) {
            $this->status = self::STATUS_OVERDUE;
            $this->save();
        }
    }
}
