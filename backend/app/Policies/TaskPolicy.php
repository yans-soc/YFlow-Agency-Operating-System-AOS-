<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\Task;

class TaskPolicy
{
    public function viewAny(Person $user): bool
    {
        return true;
    }

    public function view(Person $user, Task $task): bool
    {
        $project = $task->stage->workflow->project;
        
        return $user->workspace_id === $project->workspace_id || 
               $user->id === $task->created_by ||
               $task->assignees->contains('id', $user->id) ||
               $user->role === 'admin';
    }

    public function create(Person $user): bool
    {
        return true;
    }

    public function update(Person $user, Task $task): bool
    {
        return $user->id === $task->created_by || $user->role === 'admin';
    }

    public function delete(Person $user, Task $task): bool
    {
        return $user->id === $task->created_by || $user->role === 'admin';
    }

    public function assign(Person $user, Task $task): bool
    {
        $project = $task->stage->workflow->project;
        
        return $user->workspace_id === $project->workspace_id || $user->role === 'admin';
    }
}