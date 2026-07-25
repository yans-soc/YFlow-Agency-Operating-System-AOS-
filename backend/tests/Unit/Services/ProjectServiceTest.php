<?php

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Workspace;
use App\Models\Person;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProjectService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProjectService();
    }

    public function test_can_create_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);

        $data = [
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Test Project',
            'description' => 'Test Description',
            'status' => 'planning',
        ];

        $project = $this->service->create($data);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Test Project', $project->name);
        $this->assertEquals('planning', $project->status);
        $this->assertNotNull($project->workflow);
        $this->assertCount(5, $project->workflow->stages);
    }

    public function test_can_update_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $data = [
            'name' => 'Updated Name',
            'status' => 'active',
        ];

        $updated = $this->service->update($project, $data);

        $this->assertEquals('Updated Name', $updated->name);
        $this->assertEquals('active', $updated->status);
    }

    public function test_can_archive_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'status' => 'active',
        ]);

        $archived = $this->service->archive($project);

        $this->assertEquals('archived', $archived->status);
    }
}