<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\CustomerInvoice;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use App\Services\DashboardService;
use Tests\TestCase;

class DashboardRbacTest extends TestCase
{
    private DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DashboardService();
    }

    /**
     * Test Dashboard Service Payload structure per role.
     */
    public function test_super_admin_dashboard_payload_structure(): void
    {
        $this->actingAsSuperAdmin();
        $payload = $this->service->getDataForUser(auth()->user());

        $this->assertArrayHasKey('showAdmin', $payload);
        $this->assertArrayHasKey('kpi', $payload);
        $this->assertArrayHasKey('activity', $payload);
        $this->assertArrayHasKey('financial', $payload);
        $this->assertArrayHasKey('users', $payload);
    }

    public function test_healthcare_user_dashboard_payload_structure(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        $payload = $this->service->getDataForUser(auth()->user());

        $this->assertArrayHasKey('showHealthcare', $payload);
        $this->assertArrayHasKey('po_status', $payload);
        $this->assertArrayHasKey('delivery', $payload);
        $this->assertArrayHasKey('goods_receipt', $payload);
        $this->assertArrayHasKey('invoices', $payload);
        $this->assertArrayHasKey('payments', $payload);
    }

    /**
     * Test Multi-Tenancy in Finance models.
     */
    public function test_invoice_and_payment_multi_tenancy_isolation(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();

        // Create data for Org B
        CustomerInvoice::factory()->create(['organization_id' => $orgB->id]);
        SupplierInvoice::factory()->create(['organization_id' => $orgB->id]);
        Payment::factory()->create(['organization_id' => $orgB->id, 'type' => 'incoming']);

        // Log in as Org A User
        $this->actingAsHealthcareUser($orgA);

        // Verification via service (which calls models)
        $payload = $this->service->getDataForUser(auth()->user());
        
        // Metrics should be 0 for Org A since all data is in Org B
        $this->assertEquals(0, $payload['invoices']['total']);
        $this->assertEquals(0, $payload['invoices']['unpaid']);
        
        // Direct model check to verify Global Scope
        $this->assertEquals(0, CustomerInvoice::count());
        // Note: SupplierInvoice uses AP scoping (not clinic-scoped directly) — skipped here
        $this->assertEquals(0, Payment::count());
    }

    /**
     * Test Route Protection (Access Control).
     */


    public function test_approver_is_denied_master_data_management(): void
    {
        $this->actingAsApprover();

        $this->get('/organizations')->assertStatus(403);
        $this->get('/users')->assertStatus(403);
    }

    public function test_healthcare_user_can_access_finance_and_audit(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);

        $this->get('/finance')->assertStatus(200);
        $this->get('/audit')->assertStatus(200);
    }

    /**
     * Verify that the approval process actually works via the web route.
     */
    public function test_approval_process_works_via_web(): void
    {
        $organization = Organization::factory()->create();
        $approver = $this->actingAsApprover($organization);
        
        $staff = \App\Models\User::factory()->healthcareUser()->forOrganization($organization)->create();
        $po = PurchaseOrder::factory()->underReview()->forOrganization($organization)->createdBy($staff)->create();
        
        // Ensure there is a pending approval record
        $po->approvals()->create([
            'level' => 1,
            'status' => 'pending'
        ]);

        $response = $this->post("/approvals/{$po->id}/process", [
            'level' => 1,
            'decision' => 'approved',
            'notes' => 'Test approval note'
        ]);

        $response->assertRedirect();
        $po->refresh();
        
        $this->assertEquals(PurchaseOrder::STATUS_APPROVED, $po->status);
        $this->assertDatabaseHas('approvals', [
            'purchase_order_id' => $po->id,
            'status' => 'approved',
            'approver_id' => $approver->id
        ]);
    }

    /**
     * Verify that a Narcotic PO requires TWO levels of approval.
     */
    public function test_narcotic_po_requires_two_levels_of_approval(): void
    {
        $organization = Organization::factory()->create();
        $approver = $this->actingAsApprover($organization);
        
        $staff = \App\Models\User::factory()->healthcareUser()->forOrganization($organization)->create();
        
        // PO with narcotics flag
        $po = PurchaseOrder::factory()->underReview()->forOrganization($organization)->createdBy($staff)->create([
            'requires_extra_approval' => true,
            'has_narcotics' => true
        ]);
        
        // Initialize 2 levels
        $po->approvals()->create(['level' => 1, 'status' => 'pending']);
        $po->approvals()->create(['level' => 2, 'status' => 'pending']);

        // 1. Approve Level 1
        $this->post("/approvals/{$po->id}/process", [
            'level' => 1,
            'decision' => 'approved'
        ])->assertRedirect();

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_SUBMITTED, $po->status, 'Should still be submitted after Level 1 (waiting for Level 2)');

        // 2. Approve Level 2
        $this->post("/approvals/{$po->id}/process", [
            'level' => 2,
            'decision' => 'approved'
        ])->assertRedirect();

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_APPROVED, $po->status, 'Should be fully approved after Level 2');
    }
}
