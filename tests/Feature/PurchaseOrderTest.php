<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Organization;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\POService;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    // -----------------------------------------------------------------------
    // helpers
    // -----------------------------------------------------------------------

    private function makeOrganizationWithSupplierAndProduct(bool $narcotic = false): array
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $product  = Product::factory()->forSupplier($supplier)->create([
            'is_narcotic' => $narcotic,
        ]);

        // Default large credit limit for tests
        \App\Models\CreditLimit::updateOrCreate(
            ['organization_id' => $organization->id],
            ['max_limit' => 1000000000, 'is_active' => true]
        );

        return [$organization, $supplier, $product];
    }

    // -----------------------------------------------------------------------
    // CREATE
    // -----------------------------------------------------------------------

    public function test_healthcare_user_can_create_po(): void
    {
        [$organization, $supplier] = $this->makeOrganizationWithSupplierAndProduct();
        $this->actingAsHealthcareUser($organization);

        $this->postJson('/api/purchase-orders', [
            'supplier_id' => $supplier->id,
            'notes'       => 'Test PO',
        ])->assertStatus(201)
            ->assertJsonPath('purchase_order.status', PurchaseOrder::STATUS_DRAFT);
    }

    public function test_approver_cannot_create_po(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $this->actingAsApprover($organization);

        $this->postJson('/api/purchase-orders', [
            'supplier_id' => $supplier->id,
        ])->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // ITEMS
    // -----------------------------------------------------------------------

    public function test_can_sync_items_on_draft_po(): void
    {
        [$organization, $supplier, $product] = $this->makeOrganizationWithSupplierAndProduct();
        $user = $this->actingAsHealthcareUser($organization);
        $po   = PurchaseOrder::factory()->draft()->forOrganization($organization)->forSupplier($supplier)->createdBy($user)->create();

        $this->putJson("/api/purchase-orders/{$po->id}/items", [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 50000],
            ],
        ])->assertOk()
            ->assertJsonPath('purchase_order.total_amount', '500000.00');
    }

    // -----------------------------------------------------------------------
    // SUBMIT
    // -----------------------------------------------------------------------

    public function test_po_cannot_be_submitted_without_items(): void
    {
        [$organization, $supplier] = $this->makeOrganizationWithSupplierAndProduct();
        $user = $this->actingAsHealthcareUser($organization);
        $po   = PurchaseOrder::factory()->draft()->forOrganization($organization)->forSupplier($supplier)->createdBy($user)->create();

        $this->postJson("/api/purchase-orders/{$po->id}/submit")
            ->assertStatus(422)
            ->assertJsonPath('errors.items.0', 'A Purchase Order must have at least one item before submission.');
    }

    public function test_non_narcotic_po_creates_one_approval(): void
    {
        [$organization, $supplier, $product] = $this->makeOrganizationWithSupplierAndProduct(narcotic: false);
        $user = $this->actingAsHealthcareUser($organization);

        /** @var PurchaseOrder $po */
        $po = PurchaseOrder::factory()->draft()->forOrganization($organization)->forSupplier($supplier)->createdBy($user)->create();
        $po->items()->create([
            'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10000, 'subtotal' => 10000,
        ]);

        $this->postJson("/api/purchase-orders/{$po->id}/submit")->assertOk();

        $this->assertDatabaseCount('approvals', 1);
        $this->assertDatabaseHas('approvals', ['purchase_order_id' => $po->id, 'level' => 1]);
    }

    public function test_narcotic_po_creates_two_approvals(): void
    {
        [$organization, $supplier, $product] = $this->makeOrganizationWithSupplierAndProduct(narcotic: true);
        $user = $this->actingAsHealthcareUser($organization);

        $po = PurchaseOrder::factory()->draft()->forOrganization($organization)->forSupplier($supplier)->createdBy($user)->create();
        $po->items()->create([
            'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 10000, 'subtotal' => 10000,
        ]);

        // Recalculate totals so has_narcotics flag is set (matches what the API service does)
        $po->recalculateTotals();
        $po->save();

        $this->postJson("/api/purchase-orders/{$po->id}/submit")->assertOk();

        $this->assertDatabaseCount('approvals', 2);
        $this->assertDatabaseHas('approvals', ['purchase_order_id' => $po->id, 'level' => 2]);
    }

    // -----------------------------------------------------------------------
    // APPROVE
    // -----------------------------------------------------------------------

    public function test_po_is_approved_after_all_levels_approved(): void
    {
        [$organization, $supplier, $product] = $this->makeOrganizationWithSupplierAndProduct();
        $creator  = User::factory()->healthcareUser()->forOrganization($organization)->create();
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()->underReview()->forOrganization($organization)->forSupplier($supplier)->createdBy($creator)->create();
        $po->approvals()->create(['level' => 1, 'status' => Approval::STATUS_PENDING]);

        $this->postJson("/api/purchase-orders/{$po->id}/approvals/process", [
            'level'    => 1,
            'decision' => 'approved',
            'notes'    => 'OK',
        ])->assertOk();

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_APPROVED, $po->status);
    }

    public function test_rejected_po_status_becomes_rejected(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $creator  = User::factory()->healthcareUser()->forOrganization($organization)->create();
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()->underReview()->forOrganization($organization)->forSupplier($supplier)->createdBy($creator)->create();
        $po->approvals()->create(['level' => 1, 'status' => Approval::STATUS_PENDING]);

        $this->postJson("/api/purchase-orders/{$po->id}/approvals/process", [
            'level'    => 1,
            'decision' => 'rejected',
            'notes'    => 'Budget exceeded',
        ])->assertOk();

        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_REJECTED, $po->status);
    }

    // -----------------------------------------------------------------------
    // MULTI-TENANT ISOLATION
    // -----------------------------------------------------------------------

    public function test_organization_tenant_isolation(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $creatorB = User::factory()->healthcareUser()->forOrganization($orgB)->create();

        // PO belongs to Organization B
        PurchaseOrder::factory()->forOrganization($orgB)->forSupplier($supplier)->createdBy($creatorB)->create();

        // Organization A user logs in
        $this->actingAsHealthcareUser($orgA);

        $response = $this->getJson('/api/purchase-orders');
        $response->assertOk();
        $this->assertCount(0, $response->json('data')); // cannot see clinic B's POs
    }

    public function test_super_admin_can_see_all_pos(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $creatorA = User::factory()->healthcareUser()->forOrganization($orgA)->create();
        $creatorB = User::factory()->healthcareUser()->forOrganization($orgB)->create();

        PurchaseOrder::factory()->forOrganization($orgA)->forSupplier($supplier)->createdBy($creatorA)->create();
        PurchaseOrder::factory()->forOrganization($orgB)->forSupplier($supplier)->createdBy($creatorB)->create();

        $this->actingAsSuperAdmin();

        $response = $this->getJson('/api/purchase-orders');
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    // -----------------------------------------------------------------------
    // CREDIT CONTROL
    // -----------------------------------------------------------------------

    public function test_credit_is_reserved_on_submission(): void
    {
        [$organization, $supplier, $product] = $this->makeOrganizationWithSupplierAndProduct();
        $staff = $this->actingAsHealthcareUser($organization);
        
        // Setup credit limit (updates the one created in helper)
        \App\Models\CreditLimit::where('organization_id', $organization->id)->update(['max_limit' => 1000000]);

        $po = PurchaseOrder::factory()->draft()->forOrganization($organization)->forSupplier($supplier)->createdBy($staff)->create();
        $po->items()->create([
            'product_id' => $product->id, 'quantity' => 10, 'unit_price' => 50000, 'subtotal' => 500000,
        ]);
        $po->recalculateTotals();
        $po->save();

        $this->postJson("/api/purchase-orders/{$po->id}/submit")->assertOk();

        $this->assertDatabaseHas('credit_usages', [
            'organization_id'   => $organization->id,
            'purchase_order_id' => $po->id,
            'amount_used'       => 500000,
            'status'            => 'reserved',
        ]);
    }

    public function test_cannot_submit_po_if_credit_limit_exceeded(): void
    {
        [$organization, $supplier, $product] = $this->makeOrganizationWithSupplierAndProduct();
        $staff = $this->actingAsHealthcareUser($organization);
        
        // Setup low credit limit (updates the one created in helper)
        \App\Models\CreditLimit::where('organization_id', $organization->id)->update(['max_limit' => 100000]);

        $po = PurchaseOrder::factory()->draft()->forOrganization($organization)->forSupplier($supplier)->createdBy($staff)->create();
        $po->items()->create([
            'product_id' => $product->id, 'quantity' => 10, 'unit_price' => 50000, 'subtotal' => 500000, // Exceeds 100k
        ]);
        $po->recalculateTotals();
        $po->save();

        $response = $this->postJson("/api/purchase-orders/{$po->id}/submit");
        
        // DomainException is mapped to 422 in bootstrap/app.php
        $response->assertStatus(422); 
        $response->assertJsonPath('message', 'Tolak: Limit kredit tidak mencukupi untuk memproses PO. Tersedia: Rp 100.000');
    }
}
