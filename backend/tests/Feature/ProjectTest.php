<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($owner)->postJson('/api/v1/projects', [
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'New Project',
            'description' => 'Project Description',
            'status' => 'planning',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'New Project')
            ->assertJsonPath('data.status', 'planning');

        $this->assertDatabaseHas('projects', ['name' => 'New Project']);
    }

    public function test_can_list_projects(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        Project::factory()->count(3)->create(['workspace_id' => $workspace->id, 'owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data.data');
    }

    public function test_can_view_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $project->id);
    }

    public function test_can_update_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($owner)->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Updated Name',
            'status' => 'active',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.status', 'active');
    }

    public function test_can_delete_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_cannot_view_project_from_different_workspace(): void
    {
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace1->id]);
        $user = Person::factory()->state(['system_role' => 'contributor'])->create(['workspace_id' => $workspace2->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace1->id,
            'owner_id' => $owner->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(403);
    }
}