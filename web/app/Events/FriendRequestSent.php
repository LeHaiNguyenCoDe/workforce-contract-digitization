<?php

namespace App\Events;

use App\Models\Friendship;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendRequestSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Friendship $friendship;

    /**
     * Create a new event instance.
     */
    public function __construct(Friendship $friendship)
    {
        $this->friendship = $friendship->load(['user']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->friendship->friend_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'friend.request';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->friendship->id,
            'from_user' => [
                'id' => $this->friendship->user->id,
                'name' => $this->friendship->user->name,
                'avatar' => $this->friendship->user->avatar,
            ],
            'status' => $this->friendship->status,
            'created_at' => $this->friendship->created_at->toIso8601String(),
        ];
    }
}
