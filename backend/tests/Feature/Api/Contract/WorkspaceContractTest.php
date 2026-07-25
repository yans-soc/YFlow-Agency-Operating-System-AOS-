<?php

namespace Tests\Feature\Api\Contract;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceContractTest extends TestCase
{
    use RefreshDatabase;

    private Person $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Person::factory()->create(['system_role' => 'super_admin']);
    }

    public function test_index_returns_list_of_workspaces_for_authenticated_user(): void
    {
        Workspace::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->getJson('/api/v1/workspaces');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'logo',
                            'timezone',
                            'status',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);
    }

    public function test_index_returns_401_for_unauthenticated_user(): void
    {
        $response = $this->getJson('/api/v1/workspaces');
        $response->assertStatus(401);
    }

    public function test_store_creates_new_workspace_with_valid_data(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/workspaces', [
            'name' => 'New Workspace',
            'slug' => 'new-workspace',
            'timezone' => 'Asia/Jakarta',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'timezone',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonFragment([
                'name' => 'New Workspace',
            ]);
    }

    public function test_store_returns_422_for_missing_name(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/workspaces', [
            'slug' => 'new-workspace',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_for_name_too_long(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/workspaces', [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_show_returns_workspace_details(): void
    {
        $workspace = Workspace::factory()->create();
        $this->user->update(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/workspaces/{$workspace->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'logo',
                    'timezone',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonFragment([
                'id' => $workspace->id,
            ]);
    }

    public function test_show_returns_404_for_non_existent_workspace(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/workspaces/99999');
        $response->assertStatus(404);
    }

    public function test_show_returns_403_for_workspace_not_owned_by_user(): void
    {
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $user = Person::factory()->create(['system_role' => 'contributor', 'workspace_id' => $otherWorkspace->id]);

        $response = $this->actingAs($user)->getJson("/api/v1/workspaces/{$workspace->id}");
        $response->assertStatus(403);
    }

    public function test_update_updates_workspace_with_valid_data(): void
    {
        $workspace = Workspace::factory()->create();
        $this->user->update(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/workspaces/{$workspace->id}", [
            'name' => 'Updated Name',
            'timezone' => 'America/New_York',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Name',
            ]);
    }

    public function test_update_returns_404_for_non_existent_workspace(): void
    {
        $response = $this->actingAs($this->user)->putJson('/api/v1/workspaces/99999', [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(404);
    }

    public function test_update_returns_403_for_workspace_not_owned_by_user(): void
    {
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $user = Person::factory()->create(['system_role' => 'contributor', 'workspace_id' => $otherWorkspace->id]);

        $response = $this->actingAs($user)->putJson("/api/v1/workspaces/{$workspace->id}", [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(403);
    }

    public function test_destroy_deletes_workspace(): void
    {
        $workspace = Workspace::factory()->create();
        $this->user->update(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/workspaces/{$workspace->id}");
        $response->assertStatus(200);

        $this->assertSoftDeleted('workspaces', ['id' => $workspace->id]);
    }

    public function test_destroy_returns_404_for_non_existent_workspace(): void
    {
        $response = $this->actingAs($this->user)->deleteJson('/api/v1/workspaces/99999');
        $response->assertStatus(404);
    }

    public function test_destroy_returns_403_for_workspace_not_owned_by_user(): void
    {
        $workspace = Workspace::factory()->create();
        $otherWorkspace = Workspace::factory()->create();
        $user = Person::factory()->create(['system_role' => 'contributor', 'workspace_id' => $otherWorkspace->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/workspaces/{$workspace->id}");
        $response->assertStatus(403);
    }
}