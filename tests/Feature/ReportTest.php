<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Tests\TestCase;

class ReportTest extends TestCase
{
    public function test_super_admin_can_access_dashboard(): void
    {
        $this->actingAsSuperAdmin();

        $this->getJson('/api/reports/dashboard')
            ->assertOk()
            ->assertJsonStructure([
                'po_by_status',
                'pending_approval',
                'narcotics_pos',
                'spend' => ['total', 'this_month', 'last_month'],
                'top_suppliers',
            ]);
    }

    public function test_dashboard_status_counts_are_accurate(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $creator  = User::factory()->approver()->forOrganization($organization)->create();

        PurchaseOrder::factory()->draft()->forOrganization($organization)->forSupplier($supplier)->createdBy($creator)->count(2)->create();
        PurchaseOrder::factory()->approved()->forOrganization($organization)->forSupplier($supplier)->createdBy($creator)->count(3)->create();

        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/reports/dashboard');
        $response->assertOk();

        $byStatus = $response->json('po_by_status');
        $this->assertEquals(2, $byStatus[PurchaseOrder::STATUS_DRAFT]);
        $this->assertEquals(3, $byStatus[PurchaseOrder::STATUS_APPROVED]);
    }

    public function test_healthcare_user_dashboard_is_scoped_to_organization(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $creatorA = User::factory()->approver()->forOrganization($orgA)->create();
        $creatorB = User::factory()->approver()->forOrganization($orgB)->create();

        PurchaseOrder::factory()->draft()->forOrganization($orgA)->forSupplier($supplier)->createdBy($creatorA)->count(2)->create();
        PurchaseOrder::factory()->draft()->forOrganization($orgB)->forSupplier($supplier)->createdBy($creatorB)->count(5)->create();

        $this->actingAsHealthcareUser($orgA);

        $response = $this->getJson('/api/reports/dashboard');
        $response->assertOk();

        $byStatus = $response->json('po_by_status');
        $total = array_sum($byStatus);
        $this->assertEquals(2, $total); // only org A's POs
    }

    public function test_po_summary_is_accessible_and_paginated(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $creator  = User::factory()->approver()->forOrganization($organization)->create();

        PurchaseOrder::factory()->count(5)->forOrganization($organization)->forSupplier($supplier)->createdBy($creator)->create();

        $this->actingAsSuperAdmin();

        $this->getJson('/api/reports/po-summary')
            ->assertOk()
            ->assertJsonStructure(['data', 'meta', 'totals' => ['count', 'total_amount']]);
    }

    public function test_approver_can_access_reports(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsApprover($organization);

        // Approver has view_po permission
        $this->getJson('/api/reports/dashboard')->assertOk();
    }
}
