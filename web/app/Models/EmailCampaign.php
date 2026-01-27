<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $fillable = [
        'name',
        'subject',
        'content_html',
        'status',
        'recipients',
        'scheduled_at',
        'sent_at',
        'stats',
        'user_id',
    ];

    protected $casts = [
        'recipients' => 'array',
        'stats' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
