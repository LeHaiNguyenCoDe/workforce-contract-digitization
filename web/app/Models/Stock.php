<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BR-04.1: Inventory chỉ được tạo khi QC = PASS
 * BR-04.2: Inventory phải gắn nguồn Batch & QC
 * BR-04.3: Inventory không được sửa trực tiếp
 */
class Stock extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'available_quantity', // BR-06.1: Tồn kho có thể xuất
        'expiry_date',
        'inbound_batch_id', // BR-04.2: Truy vết Batch
        'quality_check_id', // BR-04.2: Truy vết QC
    ];

    protected $casts = [
        'quantity' => 'integer',
        'available_quantity' => 'integer',
        'expiry_date' => 'date',
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

    public function inboundBatch(): BelongsTo
    {
        return $this->belongsTo(InboundBatch::class);
    }

    public function qualityCheck(): BelongsTo
    {
        return $this->belongsTo(QualityCheck::class);
    }
}


