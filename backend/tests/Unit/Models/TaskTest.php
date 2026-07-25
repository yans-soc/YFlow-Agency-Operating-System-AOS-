<?php

namespace Tests\Unit\Models;

use App\Models\Task;
use App\Models\WorkflowStage;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_belongs_to_stage(): void
    {
        $stage = WorkflowStage::factory()->create();
        $creator = Person::factory()->create();
        $task = Task::factory()->create([
            'stage_id' => $stage->id,
            'created_by' => $creator->id,
        ]);

        $this->assertEquals($stage->id, $task->stage->id);
    }

    public function test_task_has_creator(): void
    {
        $stage = WorkflowStage::factory()->create();
        $creator = Person::factory()->create();
        $task = Task::factory()->create([
            'stage_id' => $stage->id,
            'created_by' => $creator->id,
        ]);

        $this->assertEquals($creator->id, $task->creator->id);
    }

    public function test_task_can_have_assignees(): void
    {
        $task = Task::factory()->hasAssignees(3)->create();

        $this->assertCount(3, $task->assignees);
    }

    public function test_task_can_have_checklists(): void
    {
        $task = Task::factory()->hasChecklists(5)->create();

        $this->assertCount(5, $task->checklists);
    }

    public function test_task_default_priority_is_medium(): void
    {
        $stage = WorkflowStage::factory()->create();
        $creator = Person::factory()->create();
        $task = Task::create([
            'stage_id' => $stage->id,
            'created_by' => $creator->id,
            'title' => 'Test Task',
        ]);
        $task->refresh();

        $this->assertEquals('medium', $task->priority);
    }

    public function test_task_can_be_completed(): void
    {
        $stage = WorkflowStage::factory()->create();
        $creator = Person::factory()->create();
        $task = Task::factory()->create([
            'stage_id' => $stage->id,
            'created_by' => $creator->id,
            'completed_at' => now(),
        ]);

        $this->assertNotNull($task->completed_at);
        $this->assertTrue($task->isCompleted());
    }

    public function test_task_soft_deletes(): void
    {
        $stage = WorkflowStage::factory()->create();
        $creator = Person::factory()->create();
        $task = Task::factory()->create([
            'stage_id' => $stage->id,
            'created_by' => $creator->id,
        ]);
        $task->delete();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}