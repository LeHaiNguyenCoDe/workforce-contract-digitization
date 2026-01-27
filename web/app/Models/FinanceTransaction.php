<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Helpers\Helper;

/**
 * FinanceTransaction - Thu/Chi tiền thực tế
 * Hợp nhất từ Expense model - Single source of truth cho cash flow
 * 
 * BR-FIN-01: Thu tiền bán hàng
 * BR-FIN-02: Chi tiền
 */
class FinanceTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_code',
        'fund_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'transaction_date',
        'reference_type',
        'reference_id',
        'reference_number',
        'category_id',
        'warehouse_id',
        'description',
        'payment_method',
        'attachment',
        'is_recurring',
        'recurring_period',
        'created_by',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'transaction_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    const TYPE_RECEIPT = 'receipt'; // Thu
    const TYPE_PAYMENT = 'payment'; // Chi

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Relations
    public function fund(): BelongsTo
    {
        return $this->belongsTo(Fund::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeReceipts($query)
    {
        return $query->where('type', self::TYPE_RECEIPT);
    }

    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('transaction_date', [$from, $to]);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Generate transaction code
     */
    public static function generateCode(string $type): string
    {
        $prefix = $type === 'receipt' ? 'RC' : 'PM';
        $date = now()->format('ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return sprintf('%s%s%04d', $prefix, $date, $count);
    }

    /**
     * Boot method for model events
     */
    protected static function booted(): void
    {
        static::created(function ($transaction) {
            Helper::addLog([
                'action' => 'finance_transaction.create',
                'obj_action' => json_encode([$transaction->id, $transaction->type, $transaction->amount]),
            ]);
        });
    }
}
