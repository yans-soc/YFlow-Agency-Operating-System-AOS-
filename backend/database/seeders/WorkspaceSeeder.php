<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Person;
use App\Models\Position;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Task;
use App\Models\Team;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class WorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::factory()->active()->create([
            'name' => 'YFlow Demo Workspace',
            'slug' => 'yflow-demo',
        ]);

        $departments = Department::factory(3)->create(['workspace_id' => $workspace->id]);

        $positions = Position::factory(5)->create(['workspace_id' => $workspace->id]);

        $skills = Skill::factory(10)->create(['workspace_id' => $workspace->id]);

        $admin = Person::factory()->admin()->active()->create([
            'workspace_id' => $workspace->id,
            'department_id' => $departments->first()->id,
            'position_id' => $positions->first()->id,
            'name' => 'Admin User',
            'email' => 'admin@yflow.test',
            'password' => bcrypt('password'),
        ]);

        $teamLeads = Person::factory(3)->active()->create([
            'workspace_id' => $workspace->id,
            'department_id' => fn () => $departments->random()->id,
            'position_id' => fn () => $positions->random()->id,
        ]);

        $contributors = Person::factory(10)->active()->create([
            'workspace_id' => $workspace->id,
            'department_id' => fn () => $departments->random()->id,
            'position_id' => fn () => $positions->random()->id,
        ]);

        $teams = Team::factory(3)->create([
            'department_id' => fn () => $departments->random()->id,
            'lead_id' => fn () => $teamLeads->random()->id,
        ]);

        $projects = Project::factory(5)->active()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $admin,
        ]);

        foreach ($projects as $project) {
            $workflow = Workflow::factory()->create(['project_id' => $project->id]);

            $stages = collect(['Backlog', 'To Do', 'In Progress', 'Review', 'Done'])
                ->map(function ($name, $index) use ($workflow) {
                    return WorkflowStage::factory()->create([
                        'workflow_id' => $workflow->id,
                        'name' => $name,
                        'order' => $index + 1,
                    ]);
                });

            Task::factory(10)->create([
                'stage_id' => fn () => $stages->random()->id,
                'created_by' => fn () => $contributors->random()->id,
            ]);
        }

        $allPeople = collect([$admin])->merge($teamLeads)->merge($contributors);

        foreach ($allPeople as $person) {
            $person->skills()->attach(
                $skills->random(rand(2, 5))->pluck('id')
            );
        }
    }
}