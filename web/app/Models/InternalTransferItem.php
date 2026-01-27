<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_id',
        'product_id',
        'batch_id',
        'quantity',
        'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
    ];

    /**
     * Get transfer
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InternalTransfer::class, 'transfer_id');
    }

    /**
     * Get product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get batch
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
