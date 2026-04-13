<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_super_admin_can_list_all_users(): void
    {
        $this->actingAsSuperAdmin();

        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();
        User::factory()->healthcareUser()->forOrganization($orgA)->create();
        User::factory()->approver()->forOrganization($orgB)->create();

        $response = $this->getJson('/api/users');

        $response->assertOk();
        // Super Admin + 2 created = 3 total
        $this->assertGreaterThanOrEqual(3, $response->json('total'));
    }

    public function test_healthcare_user_only_sees_own_organization_users(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();
        $this->actingAsHealthcareUser($orgA);

        User::factory()->approver()->forOrganization($orgA)->create();
        User::factory()->approver()->forOrganization($orgB)->create(); // should not appear

        $response = $this->getJson('/api/users');
        $response->assertOk();

        // All returned users must belong to orgA
        collect($response->json('data'))->each(function ($user) use ($orgA) {
            $this->assertEquals($orgA->id, $user['organization_id']);
        });
    }

    public function test_approver_cannot_list_users(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsApprover($organization);

        $this->getJson('/api/users')->assertStatus(403);
    }

    public function test_super_admin_can_view_any_user(): void
    {
        $this->actingAsSuperAdmin();
        $organization = Organization::factory()->create();
        $user   = User::factory()->approver()->forOrganization($organization)->create();

        $this->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonStructure(['user' => ['id', 'name', 'email', 'roles']]);
    }

    public function test_super_admin_can_change_user_role(): void
    {
        $this->actingAsSuperAdmin();
        $organization = Organization::factory()->create();
        $user   = User::factory()->healthcareUser()->forOrganization($organization)->create();

        $this->patchJson("/api/users/{$user->id}", [
            'role' => 'Finance',
        ])->assertOk();

        $user->refresh();
        $this->assertTrue($user->hasRole('Finance'));
        $this->assertFalse($user->hasRole('Approver'));
    }

    public function test_super_admin_can_deactivate_user(): void
    {
        $this->actingAsSuperAdmin();
        $organization = Organization::factory()->create();
        $user   = User::factory()->forOrganization($organization)->create();

        $this->deleteJson("/api/users/{$user->id}")->assertOk();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }

    public function test_healthcare_user_cannot_change_role(): void
    {
        $orgA = Organization::factory()->create();
        $this->actingAsHealthcareUser($orgA);
        $target = User::factory()->approver()->forOrganization($orgA)->create();

        // request with role field — should be ignored (only Super Admin can set via rules)
        $response = $this->patchJson("/api/users/{$target->id}", [
            'is_active' => false,
            'role'      => 'Approver', // this field is stripped by UpdateUserRequest for non-super-admin
        ]);

        $response->assertOk();
        // Role should remain unchanged
        $target->refresh();
        $this->assertTrue($target->hasRole('Approver'));
    }

    public function test_user_list_is_filterable_by_role(): void
    {
        $this->actingAsSuperAdmin();
        $organization = Organization::factory()->create();
        User::factory()->approver()->forOrganization($organization)->count(2)->create();
        User::factory()->finance()->forOrganization($organization)->count(3)->create();

        $response = $this->getJson('/api/users?role=Approver');
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }
}
