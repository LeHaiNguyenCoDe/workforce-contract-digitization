<?php

namespace App\Services;

use App\Events\FriendRequestSent;
use App\Events\NotificationCreated;
use App\Models\Friendship;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FriendService
{
    /**
     * Get all friends of a user.
     */
    public function getFriends(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        // Get friend IDs from both directions
        $friendIds = Friendship::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('friend_id', $userId);
        })
            ->where('status', 'accepted')
            ->get()
            ->map(function ($friendship) use ($userId) {
                return $friendship->user_id === $userId
                    ? $friendship->friend_id
                    : $friendship->user_id;
            });

        return User::whereIn('id', $friendIds)
            ->select(['id', 'name', 'email', 'avatar', 'last_seen_at'])
            ->paginate($perPage);
    }

    /**
     * Get pending friend requests received.
     */
    public function getPendingRequests(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Friendship::where('friend_id', $userId)
            ->where('status', 'pending')
            ->with('user:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get sent friend requests.
     */
    public function getSentRequests(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Friendship::where('user_id', $userId)
            ->where('status', 'pending')
            ->with('friend:id,name,email,avatar')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Send a friend request.
     */
    public function sendRequest(int $userId, int $friendId): Friendship|string
    {
        // Check if users are the same
        if ($userId === $friendId) {
            return 'cannot_add_self';
        }

        // Check if friendship already exists
        $existing = Friendship::where(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $userId);
        })->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return 'already_friends';
            }
            if ($existing->status === 'pending') {
                return 'request_pending';
            }
            if ($existing->status === 'blocked') {
                return 'blocked';
            }
        }

        // Create friendship request
        $friendship = Friendship::create([
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        // Create notification for the recipient
        $this->createNotification($friendId, 'friend_request', [
            'from_user_id' => $userId,
            'from_user_name' => User::find($userId)?->name,
            'friendship_id' => $friendship->id,
        ], Friendship::class, $friendship->id);

        // Event is dispatched automatically by model

        return $friendship;
    }

    /**
     * Accept a friend request.
     */
    public function acceptRequest(int $friendshipId, int $userId): Friendship|string
    {
        $friendship = Friendship::where('id', $friendshipId)
            ->where('friend_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$friendship) {
            return 'not_found';
        }

        $friendship->update(['status' => 'accepted']);

        // Notify the requester
        $this->createNotification($friendship->user_id, 'friend_accepted', [
            'user_id' => $userId,
            'user_name' => User::find($userId)?->name,
        ], Friendship::class, $friendship->id);

        return $friendship;
    }

    /**
     * Reject a friend request.
     */
    public function rejectRequest(int $friendshipId, int $userId): bool
    {
        return Friendship::where('id', $friendshipId)
            ->where('friend_id', $userId)
            ->where('status', 'pending')
            ->delete() > 0;
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancelRequest(int $friendshipId, int $userId): bool
    {
        return Friendship::where('id', $friendshipId)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->delete() > 0;
    }

    /**
     * Unfriend a user.
     */
    public function unfriend(int $userId, int $friendId): bool
    {
        return Friendship::where(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $userId)->where('friend_id', $friendId);
        })->orWhere(function ($q) use ($userId, $friendId) {
            $q->where('user_id', $friendId)->where('friend_id', $userId);
        })->where('status', 'accepted')->delete() > 0;
    }

    /**
     * Block a user.
     */
    public function blockUser(int $userId, int $blockedUserId): Friendship
    {
        // Find existing friendship or create new
        $friendship = Friendship::where(function ($q) use ($userId, $blockedUserId) {
            $q->where('user_id', $userId)->where('friend_id', $blockedUserId);
        })->orWhere(function ($q) use ($userId, $blockedUserId) {
            $q->where('user_id', $blockedUserId)->where('friend_id', $userId);
        })->first();

        if ($friendship) {
            $friendship->update(['status' => 'blocked']);
            return $friendship;
        }

        return Friendship::create([
            'user_id' => $userId,
            'friend_id' => $blockedUserId,
            'status' => 'blocked',
        ]);
    }

    /**
     * Search users to add as friends.
     * If query is empty, returns all users (except current user).
     * Returns users with their friendship status relative to current user.
     */
    public function searchUsers(int $currentUserId, ?string $query = null, int $limit = 50): Collection
    {
        $queryBuilder = User::where('id', '!=', $currentUserId)
            ->select(['id', 'name', 'email', 'avatar', 'last_seen_at']);

        if ($query && strlen($query) >= 1) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            });
        }

        $users = $queryBuilder->limit($limit)->get();

        // Get all friendships involving current user
        $friendships = Friendship::where(function ($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId)
                ->orWhere('friend_id', $currentUserId);
        })->get()->keyBy(function ($friendship) use ($currentUserId) {
            return $friendship->user_id === $currentUserId
                ? $friendship->friend_id
                : $friendship->user_id;
        });

        // Add friendship status to each user
        return $users->map(function ($user) use ($friendships, $currentUserId) {
            $friendship = $friendships->get($user->id);

            if (!$friendship) {
                $user->friendship_status = 'none';
                $user->friendship_id = null;
            } elseif ($friendship->status === 'accepted') {
                $user->friendship_status = 'accepted';
                $user->friendship_id = $friendship->id;
            } elseif ($friendship->status === 'blocked') {
                $user->friendship_status = 'blocked';
                $user->friendship_id = $friendship->id;
            } elseif ($friendship->status === 'pending') {
                // Determine if sent or received
                if ($friendship->user_id === $currentUserId) {
                    $user->friendship_status = 'sent'; // Current user sent the request
                } else {
                    $user->friendship_status = 'pending'; // Current user received the request
                }
                $user->friendship_id = $friendship->id;
            }

            return $user;
        });
    }

    /**
     * Get all users for selection (friends/group members).
     * Returns users with their friendship status relative to current user.
     */
    public function getAllUsers(int $currentUserId, int $limit = 50): Collection
    {
        $users = User::where('id', '!=', $currentUserId)
            ->select(['id', 'name', 'email', 'avatar', 'last_seen_at'])
            ->limit($limit)
            ->get();

        // Get all friendships involving current user
        $friendships = Friendship::where(function ($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId)
                ->orWhere('friend_id', $currentUserId);
        })->get()->keyBy(function ($friendship) use ($currentUserId) {
            return $friendship->user_id === $currentUserId
                ? $friendship->friend_id
                : $friendship->user_id;
        });

        // Add friendship status to each user
        return $users->map(function ($user) use ($friendships, $currentUserId) {
            $friendship = $friendships->get($user->id);

            if (!$friendship) {
                $user->friendship_status = 'none';
                $user->friendship_id = null;
            } elseif ($friendship->status === 'accepted') {
                $user->friendship_status = 'accepted';
                $user->friendship_id = $friendship->id;
            } elseif ($friendship->status === 'blocked') {
                $user->friendship_status = 'blocked';
                $user->friendship_id = $friendship->id;
            } elseif ($friendship->status === 'pending') {
                // Determine if sent or received
                if ($friendship->user_id === $currentUserId) {
                    $user->friendship_status = 'sent'; // Current user sent the request
                } else {
                    $user->friendship_status = 'pending'; // Current user received the request
                }
                $user->friendship_id = $friendship->id;
            }

            return $user;
        });
    }

    /**
     * Create a notification.
     */
    protected function createNotification(
        int $userId,
        string $type,
        array $data,
        ?string $notifiableType = null,
        ?int $notifiableId = null
    ): Notification {
        $notification = Notification::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'type' => $type,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'data' => $data,
        ]);

        event(new NotificationCreated($notification));

        return $notification;
    }
}
