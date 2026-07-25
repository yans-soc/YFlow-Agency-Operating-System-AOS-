<?php

namespace App\Events;

use App\Models\Task;
use App\Models\WorkflowStage;
use App\Models\Person;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Task $task,
        public WorkflowStage $fromStage,
        public WorkflowStage $toStage,
        public Person $movedBy,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('workspace.' . $this->task->stage->workflow->project->workspace_id),
            new PrivateChannel('project.' . $this->task->stage->workflow->project_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'task.moved';
    }

    public function broadcastWith(): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'from_stage' => $this->fromStage->name,
            'to_stage' => $this->toStage->name,
            'moved_by_name' => $this->movedBy->name,
        ];
    }
}