<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AiSession>
 */
class AiSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'user_id' => Person::factory(),
            'title' => $this->faker->sentence(3),
            'context' => $this->faker->optional()->paragraph(),
        ];
    }
}