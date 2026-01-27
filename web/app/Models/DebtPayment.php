<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DebtPayment - Payment record for AR/AP
 */
class DebtPayment extends Model
{
    protected $fillable = [
        'debt_type',
        'debt_id',
        'finance_transaction_id',
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    const TYPE_AR = 'ar';
    const TYPE_AP = 'ap';

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the related debt (AR or AP)
     */
    public function getDebtAttribute()
    {
        if ($this->debt_type === self::TYPE_AR) {
            return AccountReceivable::find($this->debt_id);
        }
        return AccountPayable::find($this->debt_id);
    }
}
