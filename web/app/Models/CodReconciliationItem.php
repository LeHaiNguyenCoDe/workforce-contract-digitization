<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodReconciliationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reconciliation_id',
        'order_id',
        'tracking_number',
        'expected_amount',
        'received_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_MATCHED = 'matched';
    const STATUS_OVER = 'over';
    const STATUS_SHORT = 'short';
    const STATUS_MISSING = 'missing';

    /**
     * Get reconciliation
     */
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(CodReconciliation::class, 'reconciliation_id');
    }

    /**
     * Get order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Set received and calculate status
     */
    public function setReceived(float $amount): void
    {
        $this->received_amount = $amount;

        if ($amount == $this->expected_amount) {
            $this->status = self::STATUS_MATCHED;
        } elseif ($amount > $this->expected_amount) {
            $this->status = self::STATUS_OVER;
        } elseif ($amount < $this->expected_amount && $amount > 0) {
            $this->status = self::STATUS_SHORT;
        } else {
            $this->status = self::STATUS_MISSING;
        }

        $this->save();
    }
}
