<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboundBatchItem extends Model
{
    protected $fillable = [
        'inbound_batch_id',
        'product_id',
        'product_variant_id',
        'quantity_received',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
    ];

    public function inboundBatch(): BelongsTo
    {
        return $this->belongsTo(InboundBatch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}


