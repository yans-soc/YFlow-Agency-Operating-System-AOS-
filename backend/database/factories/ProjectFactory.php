<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
use App\Models\Workflow;
use App\Models\WorkflowStage;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'code' => $this->faker->unique()->bothify('PRJ-#####'),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status' => 'draft',
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (\App\Models\Project $project) {
            if (!$project->owner_id) {
                $owner = Person::factory()->create(['workspace_id' => $project->workspace_id]);
                $project->owner_id = $owner->id;
            }
        })->afterCreating(function (\App\Models\Project $project) {
            if ($project->workflows()->count() === 0) {
                $workflow = Workflow::create([
                    'project_id' => $project->id,
                    'name' => $project->name . ' Workflow',
                    'status' => 'active',
                ]);

                $defaultStages = ['Backlog', 'To Do', 'In Progress', 'Review', 'Done'];

                foreach ($defaultStages as $index => $stageName) {
                    WorkflowStage::create([
                        'workflow_id' => $workflow->id,
                        'name' => $stageName,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        });
    }


    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function hasWorkflow(): static
    {
        return $this->afterCreating(function (\App\Models\Project $project) {
            if ($project->workflows()->count() === 0) {
                $workflow = Workflow::create([
                    'project_id' => $project->id,
                    'name' => $project->name . ' Workflow',
                    'status' => 'active',
                ]);

                $defaultStages = ['Backlog', 'To Do', 'In Progress', 'Review', 'Done'];

                foreach ($defaultStages as $index => $stageName) {
                    WorkflowStage::create([
                        'workflow_id' => $workflow->id,
                        'name' => $stageName,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        });
    }
}
