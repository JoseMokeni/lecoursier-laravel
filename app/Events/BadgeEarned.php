<?php

namespace App\Events;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BadgeEarned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public Badge $badge;
    public string $tenantId;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Badge $badge, string $tenantId)
    {
        $this->user = $user;
        $this->badge = $badge;
        $this->tenantId = $tenantId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('badges.' . $this->tenantId . "." . $this->user->username),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'badge.earned';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'badge' => [
                'id' => $this->badge->id,
                'name' => $this->badge->name,
                'description' => $this->badge->description,
                'icon' => $this->badge->icon,
                'category' => $this->badge->category,
                'rarity' => $this->badge->rarity,
                'points' => $this->badge->points,
            ],
            'user_id' => $this->user->id,
            'earned_at' => now()->toISOString(),
        ];
    }
}
