<?php

namespace App\Services;

use App\Models\Person;
use Illuminate\Database\Eloquent\Collection;

class PeopleService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Person::query()
            ->with(['department', 'position', 'skills']);

        if (!empty($filters['workspace_id'])) {
            $query->where('workspace_id', $filters['workspace_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Person
    {
        $person = Person::create([
            'workspace_id' => $data['workspace_id'],
            'department_id' => $data['department_id'] ?? null,
            'position_id' => $data['position_id'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'role' => $data['role'] ?? 'member',
            'status' => $data['status'] ?? 'active',
            'avatar' => $data['avatar'] ?? null,
            'bio' => $data['bio'] ?? null,
        ]);

        if (!empty($data['skill_ids'])) {
            $person->skills()->sync($data['skill_ids']);
        }

        return $person->fresh(['department', 'position', 'skills']);
    }

    public function update(Person $person, array $data): Person
    {
        $person->update([
            'department_id' => $data['department_id'] ?? $person->department_id,
            'position_id' => $data['position_id'] ?? $person->position_id,
            'name' => $data['name'] ?? $person->name,
            'email' => $data['email'] ?? $person->email,
            'role' => $data['role'] ?? $person->role,
            'status' => $data['status'] ?? $person->status,
            'avatar' => $data['avatar'] ?? $person->avatar,
            'bio' => $data['bio'] ?? $person->bio,
        ]);

        if (isset($data['skill_ids'])) {
            $person->skills()->sync($data['skill_ids']);
        }

        return $person->fresh(['department', 'position', 'skills']);
    }

    public function delete(Person $person): bool
    {
        return $person->delete();
    }

    public function findById(string $id): ?Person
    {
        return Person::with(['department', 'position', 'skills'])->find($id);
    }
}