<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_user_can_register(): void
    {
        $this->actingAsSuperAdmin(); // registration requires no pre-auth in our setup

        $organization = Organization::factory()->create();

        $response = $this->postJson('/api/auth/register', [
            'name'                  => 'New User',
            'email'                 => 'new@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'organization_id'       => $organization->id,
            'role'                  => 'Healthcare User',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'user', 'token'])
            ->assertJsonPath('user.email', 'new@example.com');
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['email' => 'test@login.com']);

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@login.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'user', 'token'])
            ->assertJsonPath('user.email', 'test@login.com');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create(['email' => 'wrong@login.com']);

        $this->postJson('/api/auth/login', [
            'email'    => 'wrong@login.com',
            'password' => 'wrongpassword',
        ])->assertStatus(422);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->inactive()->create(['email' => 'inactive@login.com']);

        $this->postJson('/api/auth/login', [
            'email'    => 'inactive@login.com',
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'Your account has been deactivated.');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/auth/logout')->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');
    }

    public function test_me_returns_authenticated_user_with_roles(): void
    {
        $user = $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'roles']]);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/auth/me')->assertStatus(401);
    }
}
