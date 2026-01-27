<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * AccountPayable - Công nợ phải trả (BR-DEBT-02)
 */
class AccountPayable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ap_code',
        'supplier_id',
        'reference_type',
        'reference_id',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'debt_id')
            ->where('debt_type', 'ap');
    }

    /**
     * Generate AP code
     */
    public static function generateCode(): string
    {
        $date = now()->format('ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return sprintf('AP%s%04d', $date, $count);
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
}
