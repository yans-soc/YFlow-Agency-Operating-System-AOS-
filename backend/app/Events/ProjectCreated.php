<?php

namespace App\Events;

use App\Models\Project;
use App\Models\Person;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Project $project,
        public Person $creator,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('workspace.' . $this->project->workspace_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'project.created';
    }

    public function broadcastWith(): array
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'creator_name' => $this->creator->name,
        ];
    }
}