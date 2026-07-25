<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\Workspace;

class WorkspacePolicy
{
    public function viewAny(Person $user): bool
    {
        return true;
    }

    public function view(Person $user, Workspace $workspace): bool
    {
        return $user->workspace_id === $workspace->id || in_array($user->system_role, ['super_admin', 'workspace_admin']);
    }

    public function create(Person $user): bool
    {
        return true;
    }

    public function update(Person $user, Workspace $workspace): bool
    {
        return $user->workspace_id === $workspace->id || in_array($user->system_role, ['super_admin', 'workspace_admin']);
    }

    public function delete(Person $user, Workspace $workspace): bool
    {
        return in_array($user->system_role, ['super_admin', 'workspace_admin']);
    }
}