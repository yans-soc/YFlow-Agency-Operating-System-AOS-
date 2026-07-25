<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Release;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReleaseFactory extends Factory
{
    protected $model = Release::class;

    public function definition(): array
    {
        return [
            'version' => $this->faker->unique()->regexify('1.0.[0-9]'),
            'release_notes' => $this->faker->optional()->paragraphs(3, true),
            'released_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'is_current' => false,
            'created_by' => Person::factory(),
        ];
    }

    public function current(): static
    {
        return $this->state(['is_current' => true]);
    }
}