<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestChatSession extends Model
{
    protected $fillable = [
        'session_token',
        'guest_name',
        'guest_contact',
        'contact_type',
        'conversation_id',
        'assigned_staff_id',
        'status',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the conversation for this session.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the assigned staff member.
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    /**
     * Check if session is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Close the session.
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Update last activity.
     */
    public function updateActivity(): bool
    {
        $this->last_activity_at = now();
        return $this->save();
    }

    /**
     * Scope for active sessions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
