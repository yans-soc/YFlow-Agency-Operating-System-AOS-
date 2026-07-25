<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'actor_id' => Person::factory(),
            'action' => $this->faker->randomElement(['created', 'updated', 'deleted', 'assigned', 'completed']),
            'subject_type' => $this->faker->randomElement(['Task', 'Project', 'Note', 'File', 'CalendarEvent']),
            'subject_id' => $this->faker->uuid(),
            'changes' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}