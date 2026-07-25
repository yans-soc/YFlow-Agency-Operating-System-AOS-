<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Position;
use App\Models\Team;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'department_id' => Department::factory(),
            'team_id' => Team::factory(),
            'position_id' => Position::factory(),
            'system_role' => $this->faker->randomElement(['super_admin', 'workspace_admin', 'operations_manager', 'project_manager', 'contributor', 'client']),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail() . '-' . uniqid(),
            'phone' => $this->faker->phoneNumber(),
            'avatar' => null,
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'system_role' => 'workspace_admin',
        ]);
    }
}