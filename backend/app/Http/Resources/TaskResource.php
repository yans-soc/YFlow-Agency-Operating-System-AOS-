<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stage_id' => $this->stage_id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'stage' => new WorkflowStageResource($this->whenLoaded('stage')),
            'creator' => new PersonResource($this->whenLoaded('creator')),
            'assignees' => PersonResource::collection($this->whenLoaded('assignees')),
            'checklists' => TaskChecklistResource::collection($this->whenLoaded('checklists')),
        ];
    }
}