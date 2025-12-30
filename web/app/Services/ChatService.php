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
     * Get conversations for a user with pagination and optimization.
     */
    public function getConversationsForUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return Conversation::forUser($userId)
            ->with([
                'latestMessage',
                'latestMessage.user:id,name,avatar',
                'users:id,name,avatar,last_seen_at',
            ])
            ->withCount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->whereHas('conversation.users', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->whereRaw('messages.created_at > COALESCE(conversation_user.last_read_at, \'1970-01-01\')');
                    })->where('user_id', '!=', $userId);
                }
            ])
            ->orderByRaw('(SELECT MAX(created_at) FROM messages WHERE messages.conversation_id = conversations.id) DESC')
            ->paginate($perPage);
    }

    /**
     * Get or create a private conversation between two users.
     */
    public function getOrCreatePrivateConversation(int $userId, int $targetUserId): Conversation
    {
        // Try to find existing private conversation
        $existing = Conversation::private()
            ->whereHas('users', fn ($q) => $q->where('user_id', $userId))
            ->whereHas('users', fn ($q) => $q->where('user_id', $targetUserId))
            ->first();

        if ($existing) {
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

            return $conversation->load('users');
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
        $message = DB::transaction(function () use ($conversationId, $userId, $content, $type, $replyToId, $attachments) {
            // Create message
            $message = Message::create([
                'conversation_id' => $conversationId,
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
            ]);

            return $message;
        });

        // Load relationships for broadcast outside transaction to be safe
        $message->load(['user:id,name,avatar', 'attachments', 'replyTo:id,content,user_id']);

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
    public function markAsRead(int $conversationId, int $userId): void
    {
        Conversation::find($conversationId)?->users()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);
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
}
