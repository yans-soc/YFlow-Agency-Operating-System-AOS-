<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'created_by' => Person::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_time' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_time' => $this->faker->dateTimeBetween('+1 hour', '+2 months'),
            'location' => $this->faker->optional()->city(),
            'type' => $this->faker->randomElement(['meeting', 'deadline', 'milestone', 'reminder', 'other']),
        ];
    }
}