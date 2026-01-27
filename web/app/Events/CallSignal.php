<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event for broadcasting WebRTC signaling data.
 */
class CallSignal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $fromUserId,
        public int $toUserId,
        public string $type, // 'offer', 'answer', 'ice-candidate'
        public array $payload
    ) {}

    public function broadcastOn(): array
    {
        // Broadcast only to the recipient user's private channel for security
        return [
            new PrivateChannel('user.' . $this->toUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'call.signal';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'from_user_id' => $this->fromUserId,
            'type' => $this->type,
            'payload' => $this->payload,
        ];
    }
}
