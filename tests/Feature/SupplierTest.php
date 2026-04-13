<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Supplier;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    public function test_authenticated_user_can_list_suppliers(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        Supplier::factory()->count(3)->create();

        $this->getJson('/api/suppliers')
            ->assertOk()
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_healthcare_user_can_create_supplier(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);

        $this->postJson('/api/suppliers', [
            'name' => 'PT Kimia Baru',
            'code' => 'KMB-001',
        ])->assertStatus(201)
            ->assertJsonPath('supplier.name', 'PT Kimia Baru');
    }

    public function test_approver_cannot_create_supplier(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsApprover($organization);

        $this->postJson('/api/suppliers', [
            'name' => 'PT Test',
            'code' => 'TST-001',
        ])->assertStatus(403);
    }

    public function test_supplier_can_be_searched(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        Supplier::factory()->create(['name' => 'PT Farmasi Jaya']);
        Supplier::factory()->create(['name' => 'PT Medika Abadi']);

        $response = $this->getJson('/api/suppliers?search=Farmasi');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_destroy_deactivates_supplier(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        $supplier = Supplier::factory()->create();

        $this->deleteJson("/api/suppliers/{$supplier->id}")
            ->assertOk();

        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'is_active' => false]);
    }

    public function test_show_loads_products_relation(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        $supplier = Supplier::factory()->create();

        $this->getJson("/api/suppliers/{$supplier->id}")
            ->assertOk()
            ->assertJsonStructure(['supplier' => ['id', 'name', 'products']]);
    }
}
