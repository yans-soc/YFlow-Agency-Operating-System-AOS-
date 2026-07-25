<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'workspace_id' => $this->workspace_id,
            'department_id' => $this->department_id,
            'position_id' => $this->position_id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'system_role' => $this->system_role,
            'status' => $this->status,
            'avatar' => $this->avatar,
            'bio' => $this->bio,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'position' => new PositionResource($this->whenLoaded('position')),
            'skills' => SkillResource::collection($this->whenLoaded('skills')),
        ];
    }
}