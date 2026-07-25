<?php

namespace App\Events;

use App\Models\Task;
use App\Models\Person;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Task $task,
        public Person $assignee,
        public Person $assignedBy,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('workspace.' . $this->task->stage->workflow->project->workspace_id),
            new PrivateChannel('person.' . $this->assignee->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'assignee_name' => $this->assignee->name,
            'assigned_by_name' => $this->assignedBy->name,
        ];
    }
}