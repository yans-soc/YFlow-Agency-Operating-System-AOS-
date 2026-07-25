<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\Release;
use Illuminate\Support\Facades\Log;

class ReleasePolicy
{
    private function isAdmin(Person $person): bool
    {
        Log::info('ReleasePolicy::isAdmin', [
            'person_id' => $person->id,
            'system_role' => $person->system_role,
        ]);
        return in_array($person->system_role, ['super_admin', 'workspace_admin']);
    }

    public function viewAny(Person $person): bool
    {
        return true; // Everyone can list releases
    }

    public function view(Person $person, Release $release): bool
    {
        return true; // Everyone can view single release
    }

    public function create(Person $person): bool
    {
        return $this->isAdmin($person);
    }

    public function update(Person $person, Release $release): bool
    {
        return $this->isAdmin($person);
    }

    public function delete(Person $person, Release $release): bool
    {
        return $this->isAdmin($person);
    }

    public function setCurrent(Person $person, Release $release): bool
    {
        return $this->isAdmin($person);
    }
}