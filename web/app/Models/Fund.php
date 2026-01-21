<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fund - Quỹ tiền (BR-FIN-03)
 */
class Fund extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'balance',
        'initial_balance',
        'bank_name',
        'bank_account',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    const TYPE_CASH = 'cash';
    const TYPE_BANK = 'bank';
    const TYPE_OTHER = 'other';

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Update balance after transaction
     */
    public function updateBalance(float $amount, string $type): void
    {
        if ($type === 'receipt') {
            $this->balance += $amount;
        } else {
            $this->balance -= $amount;
        }
        $this->save();
    }

    /**
     * Check if can withdraw (BR-FIN-03)
     */
    public function canWithdraw(float $amount): bool
    {
        return $this->balance >= $amount;
    }
}
