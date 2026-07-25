<?php

namespace App\Services;

use App\Models\Task;
use App\Models\WorkflowStage;

class TaskService
{
    public function create(array $data): Task
    {
        $task = Task::create([
            'stage_id' => $data['stage_id'],
            'created_by' => $data['created_by'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
        ]);

        if (isset($data['assignees'])) {
            $task->assignees()->sync($data['assignees']);
        }

        return $task->fresh(['stage', 'creator', 'assignees']);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update([
            'stage_id' => $data['stage_id'] ?? $task->stage_id,
            'title' => $data['title'] ?? $task->title,
            'description' => $data['description'] ?? $task->description,
            'priority' => $data['priority'] ?? $task->priority,
            'due_date' => $data['due_date'] ?? $task->due_date,
            'completed_at' => $data['completed_at'] ?? $task->completed_at,
        ]);

        if (isset($data['assignees'])) {
            $task->assignees()->sync($data['assignees']);
        }

        return $task->fresh(['stage', 'creator', 'assignees']);
    }

    public function moveStage(Task $task, string $stageId): Task
    {
        $task->update(['stage_id' => $stageId]);
        return $task->fresh(['stage.workflow.project']);
    }

    public function toggleComplete(Task $task): Task
    {
        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);
        return $task;
    }

    public function assign(Task $task, array $personIds): Task
    {
        $task->assignees()->sync($personIds);
        return $task->fresh(['assignees']);
    }
}