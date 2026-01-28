<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponGenerationBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix',
        'quantity',
        'generated_count',
        'template',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'generated_count' => 'integer',
        'template' => 'array',
        'generated_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * User who generated
     */
    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Check if batch is completed
     */
    public function isCompleted(): bool
    {
        return $this->generated_count >= $this->quantity;
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage(): float
    {
        if ($this->quantity == 0) {
            return 0;
        }

        return round(($this->generated_count / $this->quantity) * 100, 2);
    }
}
