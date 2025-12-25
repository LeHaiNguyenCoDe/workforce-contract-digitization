<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * BR-03.1: QC bắt buộc theo Batch
 * BR-03.2: Một Batch chỉ có 1 kết quả QC chính thức
 * BR-03.3: QC phải phân loại rõ kết quả (PASS, FAIL, PARTIAL)
 * BR-03.4: QC FAIL không được tạo Inventory
 */
class QualityCheck extends Model
{
    use HasFactory;

    public const STATUS_PASS = 'pass';
    public const STATUS_FAIL = 'fail';
    public const STATUS_PARTIAL = 'partial';

    protected $fillable = [
        'inbound_batch_id',
        'warehouse_id',
        'product_id',
        'supplier_id',
        'inspector_id',
        'check_date',
        'status',
        'score',
        'quantity_passed',
        'quantity_failed',
        'notes',
        'issues',
        'is_rollback',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'issues' => 'array',
        'check_date' => 'date',
        'score' => 'integer',
        'quantity_passed' => 'integer',
        'quantity_failed' => 'integer',
        'is_rollback' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * BR-03.2: Kiểm tra xem Batch đã có QC chính thức chưa
     */
    public static function hasOfficialQC(int $inboundBatchId): bool
    {
        return self::where('inbound_batch_id', $inboundBatchId)
            ->where('is_rollback', false)
            ->exists();
    }

    public function inboundBatch(): BelongsTo
    {
        return $this->belongsTo(InboundBatch::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}
