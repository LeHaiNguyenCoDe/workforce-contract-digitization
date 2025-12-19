<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'carrier',
        'tracking_code',
        'status',
        'shipping_fee',
        'estimated_delivery_date',
    ];

    protected $casts = [
        'shipping_fee' => 'integer',
        'estimated_delivery_date' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}


