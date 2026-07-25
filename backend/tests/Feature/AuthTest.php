<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'workspace_id' => $workspace->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'person' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('people', ['email' => 'test@example.com']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrongpassword',
            'workspace_id' => $workspace->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_login(): void
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

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $workspace = Workspace::factory()->create();
        $person = Person::factory()->create([
            'workspace_id' => $workspace->id,
        ]);

        $response = $this->actingAs($person)->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $workspace = Workspace::factory()->create();
        $person = Person::factory()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Profile User',
            'email' => 'profile@example.com',
        ]);

        $response = $this->actingAs($person)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Profile User')
            ->assertJsonPath('data.email', 'profile@example.com');
    }
}