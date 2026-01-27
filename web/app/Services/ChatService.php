<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    /**
     * Get a single conversation with details.
     */
    public function getConversation(int $conversationId, int $userId): Conversation
    {
        $user = User::findOrFail($userId);
        $conversation = $user->conversations()
            ->with([
                'latestMessage',
                'latestMessage.user:id,name,avatar',
                'users:id,name,avatar,last_seen_at',
                'guestSession',
            ])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->whereHas('conversation.users', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->whereRaw('messages.created_at > COALESCE(conversation_user.last_read_at, \'1970-01-01\')');
                    })->where('user_id', '!=', $userId);
                }
            ])
            ->findOrFail($conversationId);

        if ($conversation->type === 'private' && !$conversation->is_guest) {
            $this->calculateFriendshipStatus($conversation, $userId);
        }

        return $conversation;
    }

    /**
     * Get conversations for a user with pagination and optimization.
     * @param string|null $type Filter by type: 'guest', 'private', 'group', or null for all
     */
    public function getConversationsForUser(int $userId, int $perPage = 20, ?string $type = null): LengthAwarePaginator
    {
        $user = User::findOrFail($userId);
        $query = $user->conversations()
            ->with([
                'latestMessage',
                'latestMessage.user:id,name,avatar',
                'users:id,name,avatar,last_seen_at',
                'guestSession',
            ])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->whereHas('conversation.users', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->whereRaw('messages.created_at > COALESCE(conversation_user.last_read_at, \'1970-01-01\')');
                    })->where('user_id', '!=', $userId);
                }
            ]);

        // Filter by type
        if ($type === 'guest') {
            // Check if user is admin or manager - they can see all guest chats
            $user = User::find($userId);
            $isAdminOrManager = $user && in_array($user->role, ['admin', 'manager', 'super_admin']);

            if ($isAdminOrManager) {
                // Admin/Manager can see all guest conversations
                $query->whereHas('guestSession');
            } else {
                // Regular staff only see their assigned guest conversations
                $query->whereHas('guestSession', function ($q) use ($userId) {
                    $q->where('assigned_staff_id', $userId);
                });
            }
        } elseif ($type === 'private') {
            $query->where('type', 'private')
                ->whereDoesntHave('guestSession');
        } elseif ($type === 'group') {
            $query->where('type', 'group');
        }

        $conversations = $query->orderByRaw('(SELECT MAX(created_at) FROM messages WHERE messages.conversation_id = conversations.id) DESC')
            ->paginate($perPage);

        // Add friendship status to private conversations
        $conversations->getCollection()->each(function ($conversation) use ($userId) {
            if ($conversation->type === 'private' && !$conversation->is_guest) {
                $this->calculateFriendshipStatus($conversation, $userId);
            }
        });

        return $conversations;
    }

    /**
     * Get or create a private conversation between two users.
     */
    public function getOrCreatePrivateConversation(int $userId, int $targetUserId): Conversation
    {
        // Try to find existing private conversation (internal only, not guest chats)
        $existing = Conversation::private()
            ->with('users')
            ->whereDoesntHave('guestSession')
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->whereHas('users', fn($q) => $q->where('user_id', $targetUserId))
            ->first();

        if ($existing) {
            $this->calculateFriendshipStatus($existing, $userId);
            return $existing;
        }

        // Create new private conversation
        return DB::transaction(function () use ($userId, $targetUserId) {
            $conversation = Conversation::create([
                'type' => 'private',
                'created_by' => $userId,
            ]);

            $conversation->users()->attach([
                $userId => ['role' => 'admin'],
                $targetUserId => ['role' => 'member'],
            ]);

            $conversation->load('users');
            $this->calculateFriendshipStatus($conversation, $userId);
            return $conversation;
        });
    }

    /**
     * Create a group conversation.
     */
    public function createGroupConversation(int $creatorId, string $name, array $memberIds, ?string $avatar = null): Conversation
    {
        return DB::transaction(function () use ($creatorId, $name, $memberIds, $avatar) {
            $conversation = Conversation::create([
                'name' => $name,
                'type' => 'group',
                'avatar' => $avatar,
                'created_by' => $creatorId,
            ]);

            // Add creator as admin
            $members = [$creatorId => ['role' => 'admin']];

            // Add other members
            foreach ($memberIds as $memberId) {
                if ($memberId !== $creatorId) {
                    $members[$memberId] = ['role' => 'member'];
                }
            }

            $conversation->users()->attach($members);

            return $conversation->load('users');
        });
    }

    /**
     * Get messages for a conversation with cursor-based pagination for performance.
     */
    public function getMessages(int $conversationId, int $limit = 50, ?int $beforeId = null): Collection
    {
        $query = Message::with(['user:id,name,avatar', 'attachments', 'replyTo:id,content,user_id', 'replyTo.user:id,name'])
            ->where('conversation_id', $conversationId)
            ->orderBy('id', 'desc')
            ->limit($limit);

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        return $query->get()->reverse()->values();
    }

    /**
     * Send a message with optional attachments.
     */
    public function sendMessage(
        int $conversationId,
        int $userId,
        ?string $content,
        string $type = 'text',
        ?int $replyToId = null,
        array $attachments = []
    ): Message {
        $conversation = Conversation::findOrFail($conversationId);

        // Check if blocked in private chat
        if ($conversation->type === 'private') {
            $otherUser = $conversation->users()->where('users.id', '!=', $userId)->first();
            if ($otherUser) {
                // SENDER is blocked if the OTHER user has a 'blocked' status towards them
                $iAmBlocked = \App\Models\Friendship::where('user_id', $otherUser->id)
                    ->where('friend_id', $userId)
                    ->where('status', 'blocked')
                    ->exists();

                if ($iAmBlocked) {
                    abort(403, 'Your message was blocked. This user has blocked you.');
                }
            }
        }

        $message = DB::transaction(function () use ($conversation, $userId, $content, $type, $replyToId, $attachments) {
            // Create message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userId,
                'content' => $content,
                'type' => $type,
                'reply_to_id' => $replyToId,
            ]);

            // Handle attachments
            if (!empty($attachments)) {
                foreach ($attachments as $file) {
                    $this->attachFile($message, $file);
                }
            }

            // Update conversation timestamp for sorting
            $message->conversation->touch();

            // Update last read for sender
            $message->conversation->users()->updateExistingPivot($userId, [
                'last_read_at' => now(),
                'last_read_message_id' => $message->id,
            ]);

            return $message;
        });

        // Load relationships for broadcast outside transaction to be safe
        $message->load([
            'user:id,name,avatar',
            'attachments',
            'replyTo:id,content,user_id',
            'conversation',
            'conversation.users:id'
        ]);

        // Dispatch event (will broadcast automatically)
        \Log::info('ChatService: Dispatching MessageSent event', ['message_id' => $message->id, 'conversation_id' => $message->conversation_id]);
        event(new MessageSent($message));
        \Log::info('ChatService: MessageSent event dispatched');

        return $message;
    }

    /**
     * Attach a file to a message.
     */
    protected function attachFile(Message $message, UploadedFile $file): MessageAttachment
    {
        $path = $file->store('chat-attachments/' . date('Y/m'), 'public');
        $thumbnailPath = null;

        // Generate thumbnail for images
        if (str_starts_with($file->getMimeType(), 'image/')) {
            // For now, just use the same path - you can add thumbnail generation later
            $thumbnailPath = $path;
        }

        return MessageAttachment::create([
            'message_id' => $message->id,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'thumbnail_path' => $thumbnailPath,
        ]);
    }

    /**
     * Mark conversation as read for a user.
     */
    public function markAsRead(int $conversationId, int $userId, ?int $messageId = null): void
    {
        $conversation = Conversation::find($conversationId);
        if (!$conversation) return;

        // Find latest message if ID not provided
        if (!$messageId) {
            $messageId = $conversation->messages()->orderBy('id', 'desc')->value('id');
        }

        if (!$messageId) return;

        $conversation->users()->updateExistingPivot($userId, [
            'last_read_at' => now(),
            'last_read_message_id' => $messageId,
        ]);

        // Broadcast the read event
        event(new \App\Events\MessageRead($conversationId, $userId, $messageId));
    }

    /**
     * Get attachments for a conversation.
     * @param string $type Filter by 'media' (images/videos) or 'files'
     */
    public function getConversationAttachments(int $conversationId, string $type = 'media', int $limit = 50): Collection
    {
        $query = MessageAttachment::whereHas('message', function ($q) use ($conversationId) {
            $q->where('conversation_id', $conversationId);
        })->orderBy('id', 'desc')->limit($limit);

        if ($type === 'media') {
            $query->where(function ($q) {
                $q->where('file_type', 'like', 'image/%')
                  ->orWhere('file_type', 'like', 'video/%');
            });
        } else {
            $query->where('file_type', 'not like', 'image/%')
                  ->where('file_type', 'not like', 'video/%');
        }

        return $query->get();
    }

    /**
     * Search messages in a conversation.
     */
    public function searchMessages(int $conversationId, string $query, int $limit = 50): Collection
    {
        return Message::with(['user:id,name,avatar'])
            ->where('conversation_id', $conversationId)
            ->where('content', 'like', '%' . $query . '%')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Update conversation settings for a user.
     */
    public function updateConversationSettings(int $conversationId, int $userId, array $settings): void
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        $pivotData = [];
        if (isset($settings['is_muted'])) {
            $pivotData['is_muted'] = $settings['is_muted'];
        }
        if (isset($settings['is_pinned'])) {
            $pivotData['is_pinned'] = $settings['is_pinned'];
        }
        if (isset($settings['read_receipts_enabled'])) {
            $pivotData['read_receipts_enabled'] = $settings['read_receipts_enabled'];
        }

        if (!empty($pivotData)) {
            $conversation->users()->updateExistingPivot($userId, $pivotData);
        }

        // Conversation-wide settings
        $convData = [];
        if (isset($settings['messaging_permissions']) && ($conversation->isAdmin($userId) || $conversation->type === 'private')) {
            $convData['messaging_permissions'] = $settings['messaging_permissions'];
        }
        if (isset($settings['disappearing_messages_ttl']) && ($conversation->isAdmin($userId) || $conversation->type === 'private')) {
            $convData['disappearing_messages_ttl'] = $settings['disappearing_messages_ttl'];
        }

        if (!empty($convData)) {
            $conversation->update($convData);
        }
    }

    /**
     * Block a user.
     */
    public function blockUser(int $blockerId, int $blockedId): void
    {
        \App\Models\Friendship::updateOrCreate(
            [
                'user_id' => $blockerId,
                'friend_id' => $blockedId,
            ],
            [
                'status' => 'blocked',
            ]
        );
    }

    /**
     * Unblock a user.
     */
    public function unblockUser(int $blockerId, int $blockedId): void
    {
        \App\Models\Friendship::where('user_id', $blockerId)
            ->where('friend_id', $blockedId)
            ->where('status', 'blocked')
            ->delete();
    }

    /**
     * Add members to a group conversation.
     */
    public function addMembers(Conversation $conversation, array $userIds): void
    {
        $members = [];
        foreach ($userIds as $userId) {
            $members[$userId] = ['role' => 'member'];
        }
        $conversation->users()->syncWithoutDetaching($members);
    }

    /**
     * Remove a member from a group conversation.
     */
    public function removeMember(Conversation $conversation, int $userId): void
    {
        $conversation->users()->detach($userId);
    }

    /**
     * Leave a conversation.
     */
    public function leaveConversation(int $conversationId, int $userId): void
    {
        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            return;
        }

        $conversation->users()->detach($userId);

        // If no users left, delete the conversation
        if ($conversation->users()->count() === 0) {
            $conversation->delete();
        }
    }

    /**
     * Delete a conversation for a user.
     */
    public function deleteConversation(int $conversationId, int $userId): void
    {
        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            return;
        }

        // For private chats, we could soft delete or just remove mapping
        // For now, removing the user mapping (effectively leaving)
        $conversation->users()->detach($userId);

        // If no users left, delete the conversation and its messages
        if ($conversation->users()->count() === 0) {
            $conversation->delete();
        }
    }

    /**
     * Delete a message (soft delete).
     */
    public function deleteMessage(int $messageId, int $userId): bool
    {
        $message = Message::where('id', $messageId)
            ->where('user_id', $userId)
            ->first();

        if (!$message) {
            return false;
        }

        return $message->delete();
    }

    /**
     * Calculate and attach friendship status to a private conversation.
     */
    private function calculateFriendshipStatus(Conversation $conversation, int $userId): void
    {
        $otherUser = $conversation->users->where('id', '!=', $userId)->first();
        if (!$otherUser) {
            $conversation->setAttribute('friendship_status', 'none');
            return;
        }

        $iBlockedThem = \App\Models\Friendship::where('user_id', $userId)
            ->where('friend_id', $otherUser->id)
            ->where('status', 'blocked')
            ->exists();
        
        $theyBlockedMe = \App\Models\Friendship::where('user_id', $otherUser->id)
            ->where('friend_id', $userId)
            ->where('status', 'blocked')
            ->exists();

        $status = 'none';
        if ($iBlockedThem && $theyBlockedMe) {
            $status = 'blocked_mutually';
        } elseif ($iBlockedThem) {
            $status = 'blocked_by_me';
        } elseif ($theyBlockedMe) {
            $status = 'blocked_by_them';
        }

        $conversation->setAttribute('friendship_status', $status);
    }
}
