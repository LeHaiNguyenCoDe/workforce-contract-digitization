<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPoint extends Model
{
    use HasFactory;

    protected $table = 'customer_points';

    protected $fillable = [
        'user_id',
        'available_points',
        'used_points',
        'expired_points',
        'total_earned',
    ];

    protected $casts = [
        'available_points' => 'integer',
        'used_points' => 'integer',
        'expired_points' => 'integer',
        'total_earned' => 'integer',
    ];

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
