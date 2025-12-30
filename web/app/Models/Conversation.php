<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'name',
        'type',
        'avatar',
        'created_by',
    ];

    protected $casts = [
        'type' => 'string',
    ];

    /**
     * Get the users in this conversation.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot(['role', 'last_read_at', 'is_muted', 'is_pinned'])
            ->withTimestamps();
    }

    /**
     * Get all messages in this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the latest message for preview.
     */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Get the creator of this conversation.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: Get conversations for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('users', fn ($q) => $q->where('user_id', $userId));
    }

    /**
     * Scope: Get private conversations.
     */
    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    /**
     * Scope: Get group conversations.
     */
    public function scopeGroup($query)
    {
        return $query->where('type', 'group');
    }

    /**
     * Get unread messages count for a user.
     */
    public function unreadCountFor($userId): int
    {
        $pivot = $this->users()->where('user_id', $userId)->first()?->pivot;
        if (!$pivot || !$pivot->last_read_at) {
            return $this->messages()->count();
        }

        return $this->messages()
            ->where('created_at', '>', $pivot->last_read_at)
            ->where('user_id', '!=', $userId)
            ->count();
    }

    /**
     * Check if user is admin of this conversation.
     */
    public function isAdmin($userId): bool
    {
        return $this->users()
            ->where('user_id', $userId)
            ->where('conversation_user.role', 'admin')
            ->exists();
    }
}
