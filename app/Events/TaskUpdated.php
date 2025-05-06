<?php

namespace App\Events;

use App\Http\Resources\TaskResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The task instance resource.
     *
     * @var \App\Resources\TaskResource
     */
    public TaskResource $task;

    /**
     * The id of the tenant.
     *
     * @var string
     */
    public string $tenantId;

    /**
     * Create a new event instance.
     */
    public function __construct(TaskResource $task, string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->task = $task;
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
            new Channel('tasks.' . $this->tenantId . '.' . $this->task->user->username),
        ];
    }
}
