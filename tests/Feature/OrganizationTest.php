<?php

namespace Tests\Feature;

use App\Models\Organization;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    public function test_super_admin_can_list_organizations(): void
    {
        $this->actingAsSuperAdmin();
        Organization::factory()->count(3)->create();

        $this->getJson('/api/organizations')
            ->assertOk()
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_organization_list_is_searchable(): void
    {
        $this->actingAsSuperAdmin();
        Organization::factory()->create(['name' => 'Medikindo Utama']);
        Organization::factory()->create(['name' => 'Sumber Sehat']);

        $response = $this->getJson('/api/organizations?search=Medikindo');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_super_admin_can_create_organization(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/organizations', [
            'name' => 'RS Baru',
            'code' => 'RSB-001',
            'type' => 'hospital',
        ])->assertStatus(201)
            ->assertJsonPath('organization.name', 'RS Baru');
    }

    public function test_healthcare_user_cannot_create_organization(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);

        $this->postJson('/api/organizations', [
            'name' => 'RS Lain',
            'code' => 'RSL-001',
            'type' => 'hospital',
        ])->assertStatus(403);
    }

    public function test_super_admin_can_update_organization(): void
    {
        $this->actingAsSuperAdmin();
        $organization = Organization::factory()->create(['name' => 'Old Name']);

        $this->putJson("/api/organizations/{$organization->id}", array_merge(
            $organization->toArray(),
            ['name' => 'New Name']
        ))->assertOk()
            ->assertJsonPath('organization.name', 'New Name');
    }

    public function test_destroy_deactivates_organization(): void
    {
        $this->actingAsSuperAdmin();
        $organization = Organization::factory()->create();

        $this->deleteJson("/api/organizations/{$organization->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Organization deactivated.');

        $this->assertDatabaseHas('organizations', ['id' => $organization->id, 'is_active' => false]);
    }

    public function test_code_must_be_unique(): void
    {
        $this->actingAsSuperAdmin();
        Organization::factory()->create(['code' => 'DUP-001']);

        $this->postJson('/api/organizations', [
            'name' => 'Another',
            'code' => 'DUP-001',
            'type' => 'clinic',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('code');
    }
}
