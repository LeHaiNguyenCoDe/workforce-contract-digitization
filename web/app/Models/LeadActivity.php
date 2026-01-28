<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    use HasFactory;

    const TYPE_EMAIL_SENT = 'email_sent';
    const TYPE_EMAIL_OPENED = 'email_opened';
    const TYPE_EMAIL_CLICKED = 'email_clicked';
    const TYPE_CALL_MADE = 'call_made';
    const TYPE_CALL_RECEIVED = 'call_received';
    const TYPE_MEETING_SCHEDULED = 'meeting_scheduled';
    const TYPE_MEETING_COMPLETED = 'meeting_completed';
    const TYPE_NOTE_ADDED = 'note_added';
    const TYPE_STATUS_CHANGED = 'status_changed';
    const TYPE_SCORE_CHANGED = 'score_changed';
    const TYPE_SMS_SENT = 'sms_sent';
    const TYPE_FORM_FILLED = 'form_filled';
    const TYPE_PAGE_VISITED = 'page_visited';

    protected $fillable = [
        'lead_id',
        'type',
        'description',
        'metadata',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Lead
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
