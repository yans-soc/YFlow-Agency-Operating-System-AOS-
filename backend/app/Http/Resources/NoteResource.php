<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'project_id' => $this->project_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'content' => $this->content,
            'is_pinned' => (bool) $this->is_pinned,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'creator' => new PersonResource($this->whenLoaded('creator')),
            'project' => new ProjectResource($this->whenLoaded('project')),
        ];
    }
}