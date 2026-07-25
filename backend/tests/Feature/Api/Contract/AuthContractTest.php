<?php

namespace Tests\Feature\Api\Contract;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_accepts_valid_credentials(): void
    {
        $workspace = Workspace::factory()->create();
        $person = Person::factory()->create([
            'workspace_id' => $workspace->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'person',
                ],
            ]);
    }

    public function test_login_returns_422_for_missing_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_returns_422_for_missing_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_returns_401_for_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_returns_422_for_invalid_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_accepts_valid_data(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'workspace_id' => $workspace->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'person',
                ],
            ]);
    }

    public function test_register_returns_422_for_missing_name(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'workspace_id' => $workspace->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_register_returns_422_for_mismatched_passwords(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
            'workspace_id' => $workspace->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_register_returns_422_for_duplicate_email(): void
    {
        $workspace = Workspace::factory()->create();
        Person::factory()->create([
            'workspace_id' => $workspace->id,
            'email' => 'existing@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'workspace_id' => $workspace->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_logout_returns_200_for_authenticated_user(): void
    {
        $workspace = Workspace::factory()->create();
        $person = Person::factory()->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($person)->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    public function test_logout_returns_401_for_unauthenticated_user(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');
        $response->assertStatus(401);
    }

    public function test_me_returns_current_user_data(): void
    {
        $workspace = Workspace::factory()->create();
        $person = Person::factory()->create([
            'workspace_id' => $workspace->id,
            'email' => 'profile@example.com',
            'name' => 'Profile User',
        ]);

        $response = $this->actingAs($person)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                ],
            ])
            ->assertJsonPath('data.email', 'profile@example.com');
    }

    public function test_me_returns_401_for_unauthenticated_user(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }
}