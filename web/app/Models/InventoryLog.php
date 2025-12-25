<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BR-09.1: Không được xóa Inventory Log
 * BR-09.2: Mọi biến động tồn kho đều phải có log
 */
class InventoryLog extends Model
{
    public const MOVEMENT_TYPE_INBOUND = 'inbound';
    public const MOVEMENT_TYPE_QC_PASS = 'qc_pass';
    public const MOVEMENT_TYPE_QC_FAIL = 'qc_fail';
    public const MOVEMENT_TYPE_OUTBOUND = 'outbound';
    public const MOVEMENT_TYPE_ADJUST = 'adjust';
    public const MOVEMENT_TYPE_RETURN = 'return';

    protected $table = 'inventory_logs';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'product_variant_id',
        'movement_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'user_id',
        'inbound_batch_id',
        'quality_check_id',
        'reference_type',
        'reference_id',
        'reason',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inboundBatch(): BelongsTo
    {
        return $this->belongsTo(InboundBatch::class);
    }

    public function qualityCheck(): BelongsTo
    {
        return $this->belongsTo(QualityCheck::class);
    }
}

