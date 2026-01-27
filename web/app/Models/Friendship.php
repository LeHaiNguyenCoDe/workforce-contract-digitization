<?php

namespace App\Models;

use App\Events\FriendRequestSent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Dispatch event when friendship is created (pending).
     */
    protected $dispatchesEvents = [
        'created' => FriendRequestSent::class,
    ];

    /**
     * Get the user who initiated the friendship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the friend.
     */
    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    /**
     * Scope: Get pending friendships.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Get accepted friendships.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope: Get blocked users.
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Accept the friend request.
     */
    public function accept(): bool
    {
        return $this->update(['status' => 'accepted']);
    }

    /**
     * Block the user.
     */
    public function block(): bool
    {
        return $this->update(['status' => 'blocked']);
    }
}
