<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'lead_id' => null,
            'name' => $this->faker->word() . '-' . uniqid(),
        ];
    }
}