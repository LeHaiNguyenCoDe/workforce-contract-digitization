<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StocktakeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stocktake_id',
        'product_id',
        'batch_id',
        'system_quantity',
        'actual_quantity',
        'difference',
        'reason',
    ];

    protected $casts = [
        'system_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'difference' => 'integer',
    ];

    /**
     * Get stocktake
     */
    public function stocktake(): BelongsTo
    {
        return $this->belongsTo(Stocktake::class);
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

    /**
     * Calculate difference
     */
    public function calculateDifference(): void
    {
        if ($this->actual_quantity !== null) {
            $this->difference = $this->actual_quantity - $this->system_quantity;
        }
    }

    /**
     * Has discrepancy
     */
    public function hasDiscrepancy(): bool
    {
        return $this->difference !== 0 && $this->difference !== null;
    }
}
