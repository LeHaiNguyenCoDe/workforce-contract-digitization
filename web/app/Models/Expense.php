<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @deprecated Use FinanceTransaction model instead.
 * This model is kept for backwards compatibility with legacy data.
 * All new transactions should use FinanceTransaction.
 */
class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_code',
        'category_id',
        'warehouse_id',
        'type',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'description',
        'attachment',
        'is_recurring',
        'recurring_period',
        'status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    const TYPE_EXPENSE = 'expense';
    const TYPE_INCOME = 'income';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    /**
     * Get warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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
     * Generate expense code
     */
    public static function generateCode(string $type = 'expense'): string
    {
        $prefix = $type === 'income' ? 'INC' : 'EXP';
        $date = now()->format('ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "{$prefix}{$date}{$random}";
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by date range
     */
    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }
}
