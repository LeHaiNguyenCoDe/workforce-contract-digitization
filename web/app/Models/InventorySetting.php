<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventorySetting extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'min_quantity',
        'max_quantity',
        'reorder_quantity',
        'auto_create_purchase_request',
    ];

    protected $casts = [
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'reorder_quantity' => 'integer',
        'auto_create_purchase_request' => 'boolean',
    ];

    /**
     * Get product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Check if stock is below minimum
     */
    public function isBelowMin(int $currentStock): bool
    {
        return $this->min_quantity > 0 && $currentStock < $this->min_quantity;
    }

    /**
     * Check if stock is above maximum
     */
    public function isAboveMax(int $currentStock): bool
    {
        return $this->max_quantity > 0 && $currentStock > $this->max_quantity;
    }

    /**
     * Get recommended reorder quantity
     */
    public function getRecommendedOrderQuantity(int $currentStock): int
    {
        if ($this->reorder_quantity > 0) {
            return $this->reorder_quantity;
        }
        
        // Default: order up to max or 2x min
        $targetStock = $this->max_quantity > 0 ? $this->max_quantity : ($this->min_quantity * 2);
        return max(0, $targetStock - $currentStock);
    }
}
