<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InboundBatch extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_QC_IN_PROGRESS = 'qc_in_progress';
    public const STATUS_QC_COMPLETED = 'qc_completed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'batch_number',
        'warehouse_id',
        'supplier_id',
        'created_by',
        'status',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    /**
     * BR-02.3: Batch không được sửa sau khi QC bắt đầu
     */
    public function canBeEdited(): bool
    {
        return !in_array($this->status, [
            self::STATUS_QC_IN_PROGRESS,
            self::STATUS_QC_COMPLETED,
            self::STATUS_COMPLETED,
        ]);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InboundBatchItem::class);
    }

    public function qualityCheck(): HasMany
    {
        return $this->hasMany(QualityCheck::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}


