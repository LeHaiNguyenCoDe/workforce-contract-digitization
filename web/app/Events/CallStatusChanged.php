<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event for broadcasting call status updates (ringing, accepted, ended, etc.).
 */
class CallStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $fromUserId,
        public ?int $toUserId, // Optional, can be null if broadcasting to all members of a group
        public string $status, // 'ringing', 'accepted', 'rejected', 'busy', 'ended'
        public string $callType, // 'audio', 'video'
        public array $metadata = []
    ) {}

    public function broadcastOn(): array
    {
        // If it's a specific status update for a peer (like accept/reject), send to that user
        if ($this->toUserId) {
            return [
                new PrivateChannel('user.' . $this->toUserId),
            ];
        }

        // Otherwise (like ringing or ended in a group), broadcast to the conversation channel
        return [
            new PrivateChannel('conversation.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'from_user_id' => $this->fromUserId,
            'to_user_id' => $this->toUserId,
            'status' => $this->status,
            'call_type' => $this->callType,
            'metadata' => $this->metadata,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
