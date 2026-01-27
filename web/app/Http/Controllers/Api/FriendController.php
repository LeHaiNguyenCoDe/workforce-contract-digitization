<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FriendService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    use StoreApiResponse;

    public function __construct(protected FriendService $friendService) {}

    /**
     * Get all friends.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $friends = $this->friendService->getFriends(Auth::id(), $perPage);

        return $this->successResponse($friends, __('messages.success'));
    }

    /**
     * Get pending friend requests.
     */
    public function pending(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $requests = $this->friendService->getPendingRequests(Auth::id(), $perPage);

        return $this->successResponse($requests, __('messages.success'));
    }

    /**
     * Get sent friend requests.
     */
    public function sent(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $requests = $this->friendService->getSentRequests(Auth::id(), $perPage);

        return $this->successResponse($requests, __('messages.success'));
    }

    /**
     * Send a friend request.
     */
    public function sendRequest(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $result = $this->friendService->sendRequest(Auth::id(), $request->input('user_id'));

        if (is_string($result)) {
            $messages = [
                'cannot_add_self' => __('messages.friend.cannot_add_self'),
                'already_friends' => __('messages.friend.already_friends'),
                'request_pending' => __('messages.friend.request_pending'),
                'blocked' => __('messages.friend.blocked'),
            ];

            return $this->errorResponse($messages[$result] ?? __('messages.error'), 400);
        }

        return $this->successResponse($result, __('messages.friend.request_sent'));
    }

    /**
     * Accept a friend request.
     */
    public function accept(int $friendshipId): JsonResponse
    {
        $result = $this->friendService->acceptRequest($friendshipId, Auth::id());

        if (is_string($result)) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }

        return $this->successResponse($result, __('messages.friend.accepted'));
    }

    /**
     * Reject a friend request.
     */
    public function reject(int $friendshipId): JsonResponse
    {
        $deleted = $this->friendService->rejectRequest($friendshipId, Auth::id());

        if (!$deleted) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }

        return $this->successResponse(null, __('messages.friend.rejected'));
    }

    /**
     * Cancel a sent friend request.
     */
    public function cancel(int $friendshipId): JsonResponse
    {
        $deleted = $this->friendService->cancelRequest($friendshipId, Auth::id());

        if (!$deleted) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }

        return $this->successResponse(null, __('messages.friend.cancelled'));
    }

    /**
     * Unfriend a user.
     */
    public function unfriend(int $userId): JsonResponse
    {
        $deleted = $this->friendService->unfriend(Auth::id(), $userId);

        if (!$deleted) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }

        return $this->successResponse(null, __('messages.friend.unfriended'));
    }

    /**
     * Block a user.
     */
    public function block(int $userId): JsonResponse
    {
        $friendship = $this->friendService->blockUser(Auth::id(), $userId);

        return $this->successResponse($friendship, __('messages.friend.blocked'));
    }

    /**
     * Search users to add as friends.
     * If no query provided, returns all users.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $users = $this->friendService->searchUsers(Auth::id(), $request->input('q'));

        return $this->successResponse($users, __('messages.success'));
    }
}
