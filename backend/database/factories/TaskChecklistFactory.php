<?php

namespace Database\Factories;

use App\Models\TaskChecklist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaskChecklist>
 */
class TaskChecklistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'is_completed' => false,
        ];
    }
}
