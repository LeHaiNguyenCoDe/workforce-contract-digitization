<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAccess extends Model
{
    protected $fillable = [
        'user_id',
        'access_date',
        'is_successful_login',
        'ip_address',
    ];

    protected $casts = [
        'access_date' => 'datetime',
        'is_successful_login' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


