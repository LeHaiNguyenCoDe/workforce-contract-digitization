<?php

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\Notification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * Get notifications for a user.
     */
    public function getNotifications(int $userId, int $perPage = 20, bool $unreadOnly = false): LengthAwarePaginator
    {
        $query = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->paginate($perPage);
    }

    /**
     * Get unread count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)->unread()->count();
    }

    /**
     * Create a notification.
     */
    public function create(
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

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Delete a notification.
     */
    public function delete(string $notificationId, int $userId): bool
    {
        return Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    /**
     * Delete all notifications for a user.
     */
    public function deleteAll(int $userId): int
    {
        return Notification::where('user_id', $userId)->delete();
    }
}
