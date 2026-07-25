<?php

namespace Tests\Unit\Models;

use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_workspace_has_required_fields(): void
    {
        $workspace = Workspace::factory()->create();

        $this->assertNotNull($workspace->name);
        $this->assertNotNull($workspace->slug);
        $this->assertEquals('active', $workspace->status);
    }

    public function test_workspace_can_have_departments(): void
    {
        $workspace = Workspace::factory()->hasDepartments(3)->create();

        $this->assertCount(3, $workspace->departments);
    }

    public function test_workspace_can_have_projects(): void
    {
        $workspace = Workspace::factory()->hasProjects(5)->create();

        $this->assertCount(5, $workspace->projects);
    }

    public function test_workspace_can_have_people(): void
    {
        $workspace = Workspace::factory()->hasPeople(10)->create();

        $this->assertCount(10, $workspace->people);
    }

    public function test_workspace_soft_deletes(): void
    {
        $workspace = Workspace::factory()->create();
        $workspace->delete();

        $this->assertSoftDeleted('workspaces', ['id' => $workspace->id]);
    }
}