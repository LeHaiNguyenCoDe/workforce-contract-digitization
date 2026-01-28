<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadScoreHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'score_before',
        'score_after',
        'score_change',
        'reason',
        'changed_at',
    ];

    protected $casts = [
        'score_before' => 'integer',
        'score_after' => 'integer',
        'score_change' => 'integer',
        'changed_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Lead
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
