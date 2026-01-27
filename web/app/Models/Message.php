<?php

namespace App\Models;

use App\Events\MessageSent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'type',
        'metadata',
        'reply_to_id',
        'is_edited',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_edited' => 'boolean',
    ];

    /**
     * Broadcast events when message is created.
     */
    /**
     * Broadcast events when message is created.
     */
    // protected $dispatchesEvents = [
    //     'created' => MessageSent::class,
    // ];

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user who sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the message this is replying to.
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Get attachments for this message.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Get users who have read this message.
     */
    public function readBy(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    /**
     * Scope: Get messages for a conversation ordered by date.
     */
    public function scopeInConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc');
    }

    /**
     * Check if message has attachments.
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }
}
