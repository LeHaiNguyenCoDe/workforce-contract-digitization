<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SegmentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'segment_id',
        'customer_count',
        'customers_added',
        'customers_removed',
        'calculated_at',
    ];

    protected $casts = [
        'customer_count' => 'integer',
        'customers_added' => 'integer',
        'customers_removed' => 'integer',
        'calculated_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Segment
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(CustomerSegment::class, 'segment_id');
    }
}
