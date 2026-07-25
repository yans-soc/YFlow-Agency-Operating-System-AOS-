<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Task;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_task(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);
        $stage = $project->workflow->stages()->first();

        $response = $this->actingAs($owner)->postJson('/api/v1/tasks', [
            'stage_id' => $stage->id,
            'created_by' => $owner->id,
            'title' => 'New Task',
            'description' => 'Task Description',
            'priority' => 'high',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'New Task')
            ->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    public function test_can_list_tasks(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);
        $stage = $project->workflow->stages()->first();
        Task::factory()->count(5)->create(['stage_id' => $stage->id, 'created_by' => $owner->id]);

        $response = $this->actingAs($owner)->getJson('/api/v1/tasks');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_can_update_task(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);
        $stage = $project->workflow->stages()->first();
        $task = Task::factory()->create([
            'stage_id' => $stage->id,
            'created_by' => $owner->id,
            'title' => 'Original Title',
        ]);

        $response = $this->actingAs($owner)->putJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Updated Title',
            'priority' => 'urgent',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.priority', 'urgent');
    }

    public function test_can_delete_task(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);
        $stage = $project->workflow->stages()->first();
        $task = Task::factory()->create([
            'stage_id' => $stage->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_can_move_task_to_stage(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);
        $stage1 = $project->workflow->stages()->first();
        $stage2 = $project->workflow->stages()->skip(1)->first();
        $task = Task::factory()->create([
            'stage_id' => $stage1->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->postJson("/api/v1/tasks/{$task->id}/move-stage", [
            'stage_id' => $stage2->id,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($stage2->id, $task->fresh()->stage_id);
    }

    public function test_can_toggle_task_completion(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);
        $stage = $project->workflow->stages()->first();
        $task = Task::factory()->create([
            'stage_id' => $stage->id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)->postJson("/api/v1/tasks/{$task->id}/toggle-complete");

        $response->assertStatus(200);
        $this->assertNotNull($task->fresh()->completed_at);
    }
}