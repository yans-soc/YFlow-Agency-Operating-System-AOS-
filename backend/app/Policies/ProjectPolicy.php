<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\Project;

class ProjectPolicy
{
    public function viewAny(Person $user): bool
    {
        return true;
    }

    public function view(Person $user, Project $project): bool
    {
        if ($user->id === $project->owner_id) {
            return true;
        }

        if (in_array($user->system_role, ['super_admin', 'workspace_admin'])) {
            return true;
        }

        if ($user->workspace_id === $project->workspace_id) {
            return true;
        }
        
        return false;
    }

    public function create(Person $user): bool
    {
        return true;
    }

    public function update(Person $user, Project $project): bool
    {
        if (in_array($user->system_role, ['super_admin', 'workspace_admin'])) {
            return true;
        }
        
        return $user->workspace_id === $project->workspace_id;
    }

    public function delete(Person $user, Project $project): bool
    {
        if (in_array($user->system_role, ['super_admin', 'workspace_admin'])) {
            return true;
        }

        return $user->id === $project->owner_id;
    }
}
