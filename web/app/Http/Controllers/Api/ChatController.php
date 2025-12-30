<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use App\Traits\StoreApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    use StoreApiResponse;

    public function __construct(protected ChatService $chatService)
    {
    }

    /**
     * Get all conversations for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $type = $request->input('type'); // 'guest', 'private', 'group', or null
        $conversations = $this->chatService->getConversationsForUser(Auth::id(), $perPage, $type);

        return $this->successResponse($conversations, __('messages.success'));
    }

    /**
     * Get details of a single conversation.
     */
    public function show(int $conversationId): JsonResponse
    {
        $conversation = $this->chatService->getConversation($conversationId, Auth::id());
        return $this->successResponse($conversation, __('messages.success'));
    }

    /**
     * Get or create a private conversation with another user.
     */
    public function startPrivateChat(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $conversation = $this->chatService->getOrCreatePrivateConversation(
            Auth::id(),
            $request->input('user_id')
        );

        return $this->successResponse($conversation, __('messages.success'));
    }

    /**
     * Create a group conversation.
     */
    public function createGroup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'integer|exists:users,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $avatar = null;
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar')->store('group-avatars', 'public');
        }

        $conversation = $this->chatService->createGroupConversation(
            Auth::id(),
            $request->input('name'),
            $request->input('member_ids'),
            $avatar
        );

        return $this->successResponse($conversation, __('messages.created'));
    }

    /**
     * Get messages for a conversation.
     */
    public function getMessages(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'limit' => 'nullable|integer|min:1|max:100',
            'before_id' => 'nullable|integer',
        ]);

        $messages = $this->chatService->getMessages(
            $conversationId,
            $request->input('limit', 50),
            $request->input('before_id')
        );

        // Mark as read
        $this->chatService->markAsRead($conversationId, Auth::id());

        return $this->successResponse($messages, __('messages.success'));
    }

    /**
     * Send a message.
     */
    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'content' => 'required_without:attachments|nullable|string|max:5000',
            'type' => 'nullable|in:text,image,file',
            'reply_to_id' => 'nullable|integer|exists:messages,id',
            'attachments' => 'nullable|array|max:20',
            'attachments.*' => 'file|max:10240', // 10MB max per file
        ]);

        $type = $request->input('type', 'text');
        $attachments = $request->file('attachments', []);

        // Auto-detect type based on attachments if not provided
        if (!empty($attachments) && $type === 'text') {
            $firstFile = is_array($attachments) ? ($attachments[0] ?? null) : $attachments;
            if ($firstFile instanceof UploadedFile) {
                $type = str_starts_with($firstFile->getMimeType(), 'image/') ? 'image' : 'file';
            }
        }

        $message = $this->chatService->sendMessage(
            $conversationId,
            Auth::id(),
            $request->input('content'),
            $type,
            $request->input('reply_to_id'),
            $attachments
        );

        return $this->successResponse($message, __('messages.created'));
    }

    /**
     * Mark conversation as read.
     */
    public function markAsRead(int $conversationId): JsonResponse
    {
        $this->chatService->markAsRead($conversationId, Auth::id());

        return $this->successResponse(null, __('messages.success'));
    }

    /**
     * Add members to a group.
     */
    public function addMembers(Request $request, int $conversationId): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $conversation = \App\Models\Conversation::findOrFail($conversationId);

        if (!$conversation->isAdmin(Auth::id())) {
            return $this->errorResponse(__('messages.unauthorized'), 403);
        }

        $this->chatService->addMembers($conversation, $request->input('user_ids'));

        return $this->successResponse($conversation->load('users'), __('messages.success'));
    }

    /**
     * Remove a member from a group.
     */
    public function removeMember(int $conversationId, int $userId): JsonResponse
    {
        $conversation = \App\Models\Conversation::findOrFail($conversationId);

        if (!$conversation->isAdmin(Auth::id()) && Auth::id() !== $userId) {
            return $this->errorResponse(__('messages.unauthorized'), 403);
        }

        $this->chatService->removeMember($conversation, $userId);

        return $this->successResponse(null, __('messages.success'));
    }

    /**
     * Leave a conversation.
     */
    public function leave(int $conversationId): JsonResponse
    {
        $this->chatService->leaveConversation($conversationId, Auth::id());

        return $this->successResponse(null, __('messages.success'));
    }

    /**
     * Delete a conversation.
     */
    public function deleteConversation(int $conversationId): JsonResponse
    {
        $this->chatService->deleteConversation($conversationId, Auth::id());

        return $this->successResponse(null, __('messages.deleted'));
    }

    /**
     * Delete a message.
     */
    public function deleteMessage(int $messageId): JsonResponse
    {
        $deleted = $this->chatService->deleteMessage($messageId, Auth::id());

        if (!$deleted) {
            return $this->errorResponse(__('messages.not_found'), 404);
        }

        return $this->successResponse(null, __('messages.deleted'));
    }
}
