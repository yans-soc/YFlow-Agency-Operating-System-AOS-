<?php

namespace Tests\Unit\Policies;

use App\Models\Project;
use App\Models\Workspace;
use App\Models\Person;
use App\Policies\ProjectPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected ProjectPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new ProjectPolicy();
    }

    public function test_user_can_view_project_in_same_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $user = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertTrue($this->policy->view($user, $project));
    }

    public function test_owner_can_view_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertTrue($this->policy->view($owner, $project));
    }

    public function test_admin_can_view_any_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $admin = Person::factory()->create(['workspace_id' => $workspace->id, 'system_role' => 'workspace_admin']);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertTrue($this->policy->view($admin, $project));
    }

    public function test_user_cannot_view_project_in_different_workspace(): void
    {
        $workspace1 = Workspace::factory()->create();
        $workspace2 = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace1->id]);
        $user = Person::factory()->create(['workspace_id' => $workspace2->id, 'system_role' => 'contributor']);
        $project = Project::factory()->create([
            'workspace_id' => $workspace1->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertFalse($this->policy->view($user, $project));
    }

    public function test_owner_can_update_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertTrue($this->policy->update($owner, $project));
    }

    public function test_non_owner_cannot_delete_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $user = Person::factory()->create([
            'workspace_id' => $workspace->id,
            'system_role' => 'contributor',
        ]);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertFalse($this->policy->delete($user, $project));
    }

    public function test_admin_can_delete_any_project(): void
    {
        $workspace = Workspace::factory()->create();
        $owner = Person::factory()->create(['workspace_id' => $workspace->id]);
        $admin = Person::factory()->create(['workspace_id' => $workspace->id, 'system_role' => 'workspace_admin']);
        $project = Project::factory()->create([
            'workspace_id' => $workspace->id,
            'owner_id' => $owner->id,
        ]);

        $this->assertTrue($this->policy->delete($admin, $project));
    }
}