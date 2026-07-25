<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'created_by' => Person::factory(),
            'title' => $this->faker->sentence(3),
            'content' => $this->faker->paragraphs(3, true),
        ];
    }
}