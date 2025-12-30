<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load(['user', 'attachments', 'replyTo']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];

        // Also broadcast to each user's private channel for global notifications
        $userIds = $this->message->conversation->users()->pluck('users.id')->toArray();
        foreach ($userIds as $userId) {
            // Optional: Skip the sender to reduce noise, though handleNewMessage handles duplicates
            if ($userId !== $this->message->user_id) {
                $channels[] = new PrivateChannel('user.' . $userId);
            }
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'user_id' => $this->message->user_id,
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                'avatar' => $this->message->user->avatar,
            ],
            'content' => $this->message->content,
            'type' => $this->message->type,
            'metadata' => $this->message->metadata,
            'attachments' => $this->message->attachments->map(fn ($a) => [
                'id' => $a->id,
                'file_name' => $a->file_name,
                'file_path' => $a->file_path,
                'file_type' => $a->file_type,
                'file_size' => $a->file_size,
                'thumbnail_path' => $a->thumbnail_path,
            ]),
            'reply_to' => $this->message->replyTo ? [
                'id' => $this->message->replyTo->id,
                'content' => $this->message->replyTo->content,
                'user_name' => $this->message->replyTo->user?->name,
            ] : null,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
