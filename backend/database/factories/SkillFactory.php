<?php

namespace Database\Factories;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Skill>
 */
class SkillFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'name' => $this->faker->word() . '-' . uniqid(),
            'category' => $this->faker->randomElement(['Technical', 'Soft Skills', 'Management', 'Design', 'Marketing']),
        ];
    }
}