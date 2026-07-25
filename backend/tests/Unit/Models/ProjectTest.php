<?php

namespace Tests\Unit\Models;

use App\Models\Project;
use App\Models\Workspace;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_belongs_to_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertEquals($workspace->id, $project->workspace->id);
    }

    public function test_project_has_owner(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertEquals($owner->id, $project->owner->id);
    }

    public function test_project_can_have_members(): void
    {
        $project = Project::factory()->hasMembers(5)->create();

        $this->assertCount(5, $project->members);
    }

    public function test_project_can_have_workflow(): void
    {
        $project = Project::factory()->hasWorkflow()->create();

        $this->assertNotNull($project->workflow);
    }

    public function test_project_default_status_is_draft(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertEquals('draft', $project->status);
    }

    public function test_project_soft_deletes(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);
        $project->delete();

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
}