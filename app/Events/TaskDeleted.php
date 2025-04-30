<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The task instance id.
     *
     * @var int
     */
    public int $taskId;

    /**
     * The id of the tenant.
     *
     * @var string
     */
    public string $tenantId;

    /**
     * The username of the user to whom the task belongs.
     *
     * @var string
     */
    public string $username;

    /**
     * Create a new event instance.
     */
    public function __construct(int $taskId, string $tenantId, string $username)
    {
        $this->taskId = $taskId;
        $this->tenantId = $tenantId;
        $this->username = $username;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('tasks.' . $this->tenantId),
            new Channel('tasks.' . $this->tenantId . '.' . $this->username),
        ];
    }
}
