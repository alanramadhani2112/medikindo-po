<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    // -----------------------------------------------------------------------
    // CREATE SUPPLIER
    // -----------------------------------------------------------------------

    public function test_super_admin_can_create_supplier(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/suppliers', [
            'name' => 'PT Kimia Farma',
            'code' => 'KF001',
            'address' => 'Jakarta',
            'phone' => '021-1234567',
            'email' => 'info@kimiafarma.co.id',
            'npwp' => '01.234.567.8-901.000',
            'license_number' => 'LIC-KF-2024-001',
        ])->assertStatus(201)
            ->assertJsonPath('supplier.name', 'PT Kimia Farma')
            ->assertJsonPath('supplier.code', 'KF001');
    }

    public function test_healthcare_user_cannot_create_supplier(): void
    {
        $organization = \App\Models\Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);

        $this->postJson('/api/suppliers', [
            'name' => 'PT Kimia Farma',
            'code' => 'KF001',
        ])->assertStatus(403);
    }

    public function test_create_supplier_requires_unique_code(): void
    {
        $this->actingAsSuperAdmin();
        
        Supplier::factory()->create(['code' => 'KF001']);

        $this->postJson('/api/suppliers', [
            'name' => 'PT Kimia Farma 2',
            'code' => 'KF001', // Duplicate code
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_create_supplier_validates_required_fields(): void
    {
        $this->actingAsSuperAdmin();

        $this->postJson('/api/suppliers', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'code']);
    }

    // -----------------------------------------------------------------------
    // READ SUPPLIER
    // -----------------------------------------------------------------------

    public function test_can_list_suppliers(): void
    {
        $this->actingAsSuperAdmin();
        
        Supplier::factory()->count(3)->create();

        $this->getJson('/api/suppliers')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'code', 'is_active']
                ]
            ]);
    }

    public function test_can_search_suppliers(): void
    {
        $this->actingAsSuperAdmin();
        
        Supplier::factory()->create(['name' => 'PT Kimia Farma']);
        Supplier::factory()->create(['name' => 'PT Kalbe Farma']);
        Supplier::factory()->create(['name' => 'PT Indofarma']);

        $this->getJson('/api/suppliers?search=Kimia')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'PT Kimia Farma');
    }

    public function test_can_filter_active_suppliers(): void
    {
        $this->actingAsSuperAdmin();
        
        Supplier::factory()->create(['is_active' => true]);
        Supplier::factory()->create(['is_active' => false]);

        $this->getJson('/api/suppliers?active=1')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_show_supplier_with_products(): void
    {
        $this->actingAsSuperAdmin();
        
        $supplier = Supplier::factory()->create();
        \App\Models\Product::factory()->forSupplier($supplier)->count(2)->create();

        $this->getJson("/api/suppliers/{$supplier->id}")
            ->assertStatus(200)
            ->assertJsonPath('supplier.name', $supplier->name)
            ->assertJsonCount(2, 'supplier.products');
    }

    // -----------------------------------------------------------------------
    // UPDATE SUPPLIER
    // -----------------------------------------------------------------------

    public function test_super_admin_can_update_supplier(): void
    {
        $this->actingAsSuperAdmin();
        
        $supplier = Supplier::factory()->create(['name' => 'Old Name']);

        $this->putJson("/api/suppliers/{$supplier->id}", [
            'name' => 'New Name',
            'code' => $supplier->code,
            'license_number' => $supplier->license_number,
        ])->assertStatus(200)
            ->assertJsonPath('supplier.name', 'New Name');
    }

    public function test_healthcare_user_cannot_update_supplier(): void
    {
        $organization = \App\Models\Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        
        $supplier = Supplier::factory()->create();

        $this->putJson("/api/suppliers/{$supplier->id}", [
            'name' => 'New Name',
            'code' => $supplier->code,
            'license_number' => $supplier->license_number,
        ])->assertStatus(403);
    }

    public function test_update_supplier_validates_unique_code(): void
    {
        $this->actingAsSuperAdmin();
        
        $supplier1 = Supplier::factory()->create(['code' => 'KF001']);
        $supplier2 = Supplier::factory()->create(['code' => 'KF002']);

        $this->putJson("/api/suppliers/{$supplier2->id}", [
            'name' => $supplier2->name,
            'code' => 'KF001', // Duplicate code
            'license_number' => $supplier2->license_number,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    // -----------------------------------------------------------------------
    // DELETE SUPPLIER (SOFT DELETE)
    // -----------------------------------------------------------------------

    public function test_super_admin_can_deactivate_supplier(): void
    {
        $this->actingAsSuperAdmin();
        
        $supplier = Supplier::factory()->create(['is_active' => true]);

        $this->deleteJson("/api/suppliers/{$supplier->id}")
            ->assertStatus(200)
            ->assertJsonPath('message', 'Supplier deactivated.');

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'is_active' => false,
        ]);
    }

    public function test_healthcare_user_cannot_deactivate_supplier(): void
    {
        $organization = \App\Models\Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        
        $supplier = Supplier::factory()->create();

        $this->deleteJson("/api/suppliers/{$supplier->id}")
            ->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // BUSINESS LOGIC VALIDATION
    // -----------------------------------------------------------------------

    public function test_cannot_deactivate_supplier_with_active_products(): void
    {
        $this->actingAsSuperAdmin();
        
        $supplier = Supplier::factory()->create();
        \App\Models\Product::factory()->forSupplier($supplier)->create(['is_active' => true]);

        $this->deleteJson("/api/suppliers/{$supplier->id}")
            ->assertStatus(422)
            ->assertJsonPath('error', function($message) {
                return str_contains($message, 'produk aktif');
            });
    }

    public function test_supplier_license_expiry_validation(): void
    {
        $this->actingAsSuperAdmin();

        // Test past date validation - should now fail
        $this->postJson('/api/suppliers', [
            'name' => 'PT Test',
            'code' => 'TEST001',
            'license_number' => 'LIC-TEST-001',
            'license_expiry_date' => now()->subDay()->format('Y-m-d'),
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['license_expiry_date']);
    }

    // -----------------------------------------------------------------------
    // AUDIT LOGGING
    // -----------------------------------------------------------------------

    public function test_supplier_operations_are_audited(): void
    {
        $this->actingAsSuperAdmin();

        // Create
        $response = $this->postJson('/api/suppliers', [
            'name' => 'PT Audit Test',
            'code' => 'AUDIT001',
            'license_number' => 'LIC-AUDIT-001',
        ]);
        
        $supplier = Supplier::find($response->json('supplier.id'));

        // Update
        $this->putJson("/api/suppliers/{$supplier->id}", [
            'name' => 'PT Audit Test Updated',
            'code' => 'AUDIT001',
            'license_number' => 'LIC-AUDIT-001',
        ]);

        // Deactivate
        $this->deleteJson("/api/suppliers/{$supplier->id}");

        // Check audit logs exist (assuming AuditService is used)
        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'App\\Models\\Supplier',
            'entity_id' => $supplier->id,
        ]);
    }
}