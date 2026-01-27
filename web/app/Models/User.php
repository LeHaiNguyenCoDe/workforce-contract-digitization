<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'created_by',
        'updated_by',
        'deleted_by',
        'active',
        'language',
        'avatar',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withTimestamps();
    }

    /**
     * Get the rights that belong to the user.
     */
    public function rights(): BelongsToMany
    {
        return $this->belongsToMany(Right::class, 'user_right')
            ->withPivot('suppress')
            ->withTimestamps();
    }

    // ==================== CHAT RELATIONSHIPS ====================

    /**
     * Get conversations the user is part of.
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')
            ->withPivot(['role', 'last_read_at', 'last_read_message_id', 'is_muted', 'is_pinned', 'read_receipts_enabled'])
            ->withTimestamps()
            ->orderByPivot('updated_at', 'desc');
    }

    /**
     * Get messages sent by this user.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // ==================== FRIENDSHIP RELATIONSHIPS ====================

    /**
     * Get friendships initiated by this user.
     */
    public function friendshipsInitiated(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Get friendships received by this user.
     */
    public function friendshipsReceived(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    /**
     * Get all accepted friends.
     */
    public function friends()
    {
        $initiated = $this->friendshipsInitiated()
            ->where('status', 'accepted')
            ->with('friend')
            ->get()
            ->pluck('friend');

        $received = $this->friendshipsReceived()
            ->where('status', 'accepted')
            ->with('user')
            ->get()
            ->pluck('user');

        return $initiated->merge($received);
    }

    /**
     * Get pending friend requests received.
     */
    public function pendingFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id')
            ->where('status', 'pending');
    }

    /**
     * Check if user is friends with another user.
     */
    public function isFriendsWith(int $userId): bool
    {
        return Friendship::where(function ($q) use ($userId) {
            $q->where('user_id', $this->id)->where('friend_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('friend_id', $this->id);
        })->where('status', 'accepted')->exists();
    }

    // ==================== NOTIFICATION RELATIONSHIPS ====================

    /**
     * Get user's notifications.
     */
    public function chatNotifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->chatNotifications()->unread()->count();
    }

    /**
     * Check if user is online (seen in last 5 minutes).
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    /**
     * Get user's membership information.
     * 
     * @return HasOne
     */
    public function membership(): HasOne
    {
        return $this->hasOne(CustomerMembership::class, 'user_id');
    }
}

