<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use StoreApiResponse;

    public function __construct(protected NotificationService $notificationService) {}

    /**
     * Get notifications for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $unreadOnly = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getNotifications(Auth::id(), $perPage, $unreadOnly);

        return $this->successResponse($notifications, __('messages.success'));
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount(): JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(Auth::id());

        return $this->successResponse(['count' => $count], __('messages.success'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $notificationId): JsonResponse
    {
        $marked = $this->notificationService->markAsRead($notificationId, Auth::id());

        if (!$marked) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }

        return $this->successResponse(null, __('messages.success'));
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead(Auth::id());

        return $this->successResponse(['marked_count' => $count], __('messages.success'));
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $notificationId): JsonResponse
    {
        $deleted = $this->notificationService->delete($notificationId, Auth::id());

        if (!$deleted) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }

        return $this->successResponse(null, __('messages.deleted'));
    }

    /**
     * Delete all notifications.
     */
    public function destroyAll(): JsonResponse
    {
        $count = $this->notificationService->deleteAll(Auth::id());

        return $this->successResponse(['deleted_count' => $count], __('messages.deleted'));
    }
}
