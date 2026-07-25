<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Release;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseTest extends TestCase
{
    use RefreshDatabase;

    protected Person $adminUser;
    protected Person $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user with super_admin system_role
        $this->adminUser = Person::factory()->create(['system_role' => 'super_admin']);
        // Create regular user with contributor role
        $this->regularUser = Person::factory()->create(['system_role' => 'contributor']);
    }

    public function test_admin_can_list_releases(): void
    {
        Release::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson('/api/v1/releases');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'version', 'formatted_version', 'released_at', 'is_current']
                ]
            ]);
    }

    public function test_regular_user_can_list_releases(): void
    {
        Release::factory()->count(3)->create();

        $response = $this->actingAs($this->regularUser)
            ->getJson('/api/v1/releases');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'version', 'formatted_version', 'released_at', 'is_current']
                ]
            ]);
    }

    public function test_admin_can_create_release(): void
    {
        $data = [
            'version' => '1.0.0',
            'release_notes' => 'Initial release',
            'released_at' => now()->format('Y-m-d'),
            'is_current' => true,
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/releases', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.version', '1.0.0')
            ->assertJsonPath('data.is_current', true);

        $this->assertDatabaseHas('releases', [
            'version' => '1.0.0',
            'is_current' => true,
        ]);
    }

    public function test_version_format_validation(): void
    {
        $data = [
            'version' => 'invalid-version',
            'released_at' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/api/v1/releases', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['version']);
    }

    public function test_valid_version_formats(): void
    {
        $validVersions = ['1.0.0', '2.1.0', '1.0.0-alpha', '3.2.1-beta'];

        foreach ($validVersions as $version) {
            $data = [
                'version' => $version,
                'released_at' => now()->format('Y-m-d'),
            ];

            $response = $this->actingAs($this->adminUser)
                ->postJson('/api/v1/releases', $data);

            $response->assertStatus(201);
            
            Release::where('version', $version)->delete();
        }
    }

    public function test_admin_can_update_release(): void
    {
        $release = Release::factory()->create(['version' => '1.0.0']);

        $data = [
            'release_notes' => 'Updated notes',
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson("/api/v1/releases/{$release->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.release_notes', 'Updated notes');
    }

    public function test_admin_can_delete_non_current_release(): void
    {
        $release = Release::factory()->create(['is_current' => false]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/releases/{$release->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('releases', ['id' => $release->id]);
    }

    public function test_cannot_delete_current_release(): void
    {
        $release = Release::factory()->create(['is_current' => true]);

        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/api/v1/releases/{$release->id}");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Cannot delete the current release. Set another release as current first.');
    }

    public function test_admin_can_set_current_release(): void
    {
        $release = Release::factory()->create(['is_current' => false]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/api/v1/releases/{$release->id}/set-current");

        $response->assertStatus(200)
            ->assertJsonPath('data.is_current', true);

        $this->assertTrue($release->fresh()->is_current);
    }

    public function test_public_can_access_current_version(): void
    {
        Release::factory()->create([
            'version' => '1.0.0',
            'is_current' => true,
        ]);

        $response = $this->getJson('/api/v1/version/current');

        $response->assertStatus(200)
            ->assertJsonPath('data.version', '1.0.0')
            ->assertJsonStructure([
                'data' => ['version', 'formatted_version', 'released_at', 'release_notes']
            ]);
    }

    public function test_admin_can_view_single_release(): void
    {
        $release = Release::factory()->create();

        $response = $this->actingAs($this->adminUser)
            ->getJson("/api/v1/releases/{$release->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $release->id);
    }
}