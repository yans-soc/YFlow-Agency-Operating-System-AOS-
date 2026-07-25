<?php

namespace Database\Factories;

use App\Models\WorkflowStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkflowStage>
 */
class WorkflowStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => \App\Models\Workflow::factory(),
            'name' => $this->faker->word(),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }
}
