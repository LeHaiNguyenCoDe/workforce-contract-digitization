<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodReconciliation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reconciliation_code',
        'shipping_partner',
        'period_from',
        'period_to',
        'expected_amount',
        'received_amount',
        'difference',
        'status',
        'notes',
        'reconciled_at',
        'created_by',
        'reconciled_by',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'expected_amount' => 'decimal:2',
        'received_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'reconciled_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_MATCHED = 'matched';
    const STATUS_DISCREPANCY = 'discrepancy';
    const STATUS_RESOLVED = 'resolved';

    /**
     * Get items
     */
    public function items(): HasMany
    {
        return $this->hasMany(CodReconciliationItem::class, 'reconciliation_id');
    }

    /**
     * Get creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate code
     */
    public static function generateCode(): string
    {
        $prefix = 'COD';
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}{$date}{$random}";
    }

    /**
     * Calculate totals
     */
    public function calculateTotals(): void
    {
        $this->expected_amount = $this->items()->sum('expected_amount');
        $this->received_amount = $this->items()->whereNotNull('received_amount')->sum('received_amount');
        $this->difference = $this->received_amount - $this->expected_amount;

        if ($this->difference == 0) {
            $this->status = self::STATUS_MATCHED;
        } else {
            $this->status = self::STATUS_DISCREPANCY;
        }

        $this->save();
    }
}
