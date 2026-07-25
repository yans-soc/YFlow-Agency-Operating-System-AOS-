<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson('/api/v1/projects');
        $response->assertStatus(401);
    }

    public function test_invalid_email_format_is_rejected(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'workspace_id' => $workspace->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_short_password_is_rejected(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'workspace_id' => $workspace->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_missing_required_fields_returns_422(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($owner)->postJson('/api/v1/projects', [
            'name' => 'Project without required fields',
        ]);

        $response->assertStatus(422);
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($owner)->postJson('/api/v1/projects', [
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Test Project',
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
    }

    public function test_invalid_priority_value_is_rejected(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = \App\Models\Project::factory()->hasWorkflow()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);
        $stage = $project->workflow->stages()->first();

        $response = $this->actingAs($owner)->postJson('/api/v1/tasks', [
            'stage_id' => $stage->id,
            'created_by' => $owner->id,
            'title' => 'Test Task',
            'priority' => 'invalid_priority',
        ]);

        $response->assertStatus(422);
    }

    public function test_nonexistent_foreign_key_is_rejected(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($owner)->postJson('/api/v1/projects', [
            'workspace_id' => '00000000-0000-0000-0000-000000000000',
            'owner_id' => $owner->id,
            'name' => 'Test Project',
        ]);

        $response->assertStatus(422);
    }

    public function test_date_validation_works(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($owner)->postJson('/api/v1/projects', [
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
            'name' => 'Test Project',
            'start_date' => '2025-12-31',
            'end_date' => '2025-01-01',
        ]);

        $response->assertStatus(422);
    }
}