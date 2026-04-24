<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Organization;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\ApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected ApprovalService $approvalService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->approvalService = app(ApprovalService::class);
    }

    // -----------------------------------------------------------------------
    // APPROVAL INDEX TESTS
    // -----------------------------------------------------------------------

    public function test_approver_can_view_approval_index(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->create();

        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $response = $this->get(route('web.approvals.index'));

        $response->assertStatus(200);
        $response->assertViewIs('approvals.index');
        $response->assertViewHas(['pendingApprovals', 'counts', 'tab']);
        $response->assertSee($po->po_number);
    }

    public function test_healthcare_user_cannot_access_approval_index(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $response = $this->get(route('web.approvals.index'));

        $response->assertStatus(403);
    }

    public function test_finance_user_cannot_access_approval_index(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $response = $this->get(route('web.approvals.index'));

        $response->assertStatus(403);
    }

    public function test_can_filter_approvals_by_tab(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsApprover($organization);

        // Create pending PO
        $pendingPO = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->create();

        $pendingPO->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        // Create approved PO
        $approvedPO = PurchaseOrder::factory()
            ->state(['status' => PurchaseOrder::STATUS_APPROVED])
            ->forOrganization($organization)
            ->create();

        $approvedPO->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_APPROVED,
            'approver_id' => $user->id,
            'actioned_at' => now()
        ]);

        // Test pending tab (default)
        $response = $this->get(route('web.approvals.index', ['tab' => 'pending']));
        $response->assertStatus(200);
        $approvals = $response->viewData('pendingApprovals');
        $this->assertTrue($approvals->contains($pendingPO));
        $this->assertFalse($approvals->contains($approvedPO));

        // Test history tab
        $response = $this->get(route('web.approvals.index', ['tab' => 'history']));
        $response->assertStatus(200);
        $approvals = $response->viewData('pendingApprovals');
        $this->assertFalse($approvals->contains($pendingPO));
        $this->assertTrue($approvals->contains($approvedPO));
    }

    public function test_can_search_approvals(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsApprover($organization);

        $supplier = Supplier::factory()->create(['name' => 'Test Supplier ABC']);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->forSupplier($supplier)
            ->state(['po_number' => 'PO-SEARCH-123'])
            ->create();

        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        // Search by PO number
        $response = $this->get(route('web.approvals.index', ['search' => 'SEARCH']));
        $response->assertStatus(200);
        $approvals = $response->viewData('pendingApprovals');
        $this->assertTrue($approvals->contains($po));

        // Search by supplier name
        $response = $this->get(route('web.approvals.index', ['search' => 'ABC']));
        $response->assertStatus(200);
        $approvals = $response->viewData('pendingApprovals');
        $this->assertTrue($approvals->contains($po));
    }

    public function test_approval_counts_are_calculated_correctly(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsApprover($organization);

        // Create 2 pending POs
        for ($i = 0; $i < 2; $i++) {
            $po = PurchaseOrder::factory()
                ->underReview()
                ->forOrganization($organization)
                ->create();

            $po->approvals()->create([
                'level' => Approval::LEVEL_STANDARD,
                'status' => Approval::STATUS_PENDING
            ]);
        }

        // Create 1 approved PO
        $approvedPO = PurchaseOrder::factory()
            ->state(['status' => PurchaseOrder::STATUS_APPROVED])
            ->forOrganization($organization)
            ->create();

        $response = $this->get(route('web.approvals.index'));
        $response->assertStatus(200);

        $counts = $response->viewData('counts');
        $this->assertEquals(2, $counts['pending']);
        $this->assertEquals(1, $counts['history']);
    }

    // -----------------------------------------------------------------------
    // APPROVAL PROCESSING TESTS
    // -----------------------------------------------------------------------

    public function test_can_approve_standard_po(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($creator)
            ->create();

        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'approved',
            'notes' => 'Approved for testing'
        ]);

        $response->assertRedirect(route('web.approvals.index'));
        $response->assertSessionHas('success');

        // Verify approval was processed
        $this->assertDatabaseHas('approvals', [
            'purchase_order_id' => $po->id,
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_APPROVED,
            'approver_id' => $approver->id,
            'notes' => 'Approved for testing'
        ]);

        // Verify PO status was updated
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrder::STATUS_APPROVED
        ]);
    }

    public function test_can_reject_po(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($creator)
            ->create();

        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'rejected',
            'notes' => 'Insufficient justification'
        ]);

        $response->assertRedirect(route('web.approvals.index'));
        $response->assertSessionHas('error');

        // Verify approval was processed
        $this->assertDatabaseHas('approvals', [
            'purchase_order_id' => $po->id,
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_REJECTED,
            'approver_id' => $approver->id,
            'notes' => 'Insufficient justification'
        ]);

        // Verify PO status was updated
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrder::STATUS_REJECTED
        ]);
    }

    public function test_cannot_approve_own_po(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($user) // Same user created and trying to approve
            ->create();

        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'approved',
            'notes' => 'Self approval attempt'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verify approval was NOT processed
        $this->assertDatabaseHas('approvals', [
            'purchase_order_id' => $po->id,
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING,
            'approver_id' => null
        ]);

        // Verify PO status was NOT updated
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrder::STATUS_SUBMITTED
        ]);
    }

    public function test_narcotic_po_requires_two_level_approval(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->actingAsApprover($organization);

        $supplier = Supplier::factory()->create();
        $product = Product::factory()->narcotic()->create();

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->forSupplier($supplier)
            ->createdBy($creator)
            ->state(['requires_extra_approval' => true])
            ->create();

        // Create both approval levels
        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $po->approvals()->create([
            'level' => Approval::LEVEL_NARCOTICS,
            'status' => Approval::STATUS_PENDING
        ]);

        // Approve level 1
        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'approved',
            'notes' => 'Level 1 approved'
        ]);

        $response->assertRedirect(route('web.approvals.index'));
        $response->assertSessionHas('success');

        // PO should still be under review (waiting for level 2)
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrder::STATUS_SUBMITTED
        ]);

        // Approve level 2
        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_NARCOTICS,
            'decision' => 'approved',
            'notes' => 'Level 2 approved'
        ]);

        $response->assertRedirect(route('web.approvals.index'));
        $response->assertSessionHas('success');

        // Now PO should be fully approved
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrder::STATUS_APPROVED
        ]);
    }

    public function test_rejection_at_any_level_rejects_entire_po(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($creator)
            ->state(['requires_extra_approval' => true])
            ->create();

        // Create both approval levels
        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $po->approvals()->create([
            'level' => Approval::LEVEL_NARCOTICS,
            'status' => Approval::STATUS_PENDING
        ]);

        // Reject at level 1
        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'rejected',
            'notes' => 'Rejected at level 1'
        ]);

        $response->assertRedirect(route('web.approvals.index'));
        $response->assertSessionHas('error');

        // Entire PO should be rejected
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrder::STATUS_REJECTED
        ]);

        // Level 2 should still be pending (not processed)
        $this->assertDatabaseHas('approvals', [
            'purchase_order_id' => $po->id,
            'level' => Approval::LEVEL_NARCOTICS,
            'status' => Approval::STATUS_PENDING,
            'approver_id' => null
        ]);
    }

    // -----------------------------------------------------------------------
    // APPROVAL SERVICE TESTS
    // -----------------------------------------------------------------------

    public function test_approval_service_initializes_approvals_correctly(): void
    {
        $organization = Organization::factory()->create();

        // Standard PO (no extra approval needed)
        $standardPO = PurchaseOrder::factory()
            ->forOrganization($organization)
            ->state(['requires_extra_approval' => false])
            ->create();

        $this->approvalService->initializeApprovals($standardPO);

        $this->assertDatabaseCount('approvals', 1);
        $this->assertDatabaseHas('approvals', [
            'purchase_order_id' => $standardPO->id,
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        // Narcotic PO (extra approval needed)
        $narcoticPO = PurchaseOrder::factory()
            ->forOrganization($organization)
            ->state(['requires_extra_approval' => true])
            ->create();

        $this->approvalService->initializeApprovals($narcoticPO);

        $this->assertDatabaseCount('approvals', 3); // 1 from previous + 2 new
        $this->assertDatabaseHas('approvals', [
            'purchase_order_id' => $narcoticPO->id,
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);
        $this->assertDatabaseHas('approvals', [
            'purchase_order_id' => $narcoticPO->id,
            'level' => Approval::LEVEL_NARCOTICS,
            'status' => Approval::STATUS_PENDING
        ]);
    }

    public function test_approval_service_prevents_duplicate_approvals(): void
    {
        $organization = Organization::factory()->create();
        $po = PurchaseOrder::factory()
            ->forOrganization($organization)
            ->state(['requires_extra_approval' => true])
            ->create();

        // Initialize approvals twice
        $this->approvalService->initializeApprovals($po);
        $this->approvalService->initializeApprovals($po);

        // Should still only have 2 approvals (no duplicates)
        $this->assertDatabaseCount('approvals', 2);
    }

    public function test_approval_service_validates_pending_approval_exists(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->createApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($creator)
            ->create();

        // No approval record exists
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->approvalService->process(
            $po,
            $approver,
            Approval::LEVEL_STANDARD,
            Approval::STATUS_APPROVED
        );
    }

    // -----------------------------------------------------------------------
    // MULTI-TENANT SECURITY TESTS
    // -----------------------------------------------------------------------

    public function test_approver_can_see_all_organization_pos(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $user = $this->actingAsApprover($org1);

        // Create PO for org1
        $po1 = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($org1)
            ->create();

        $po1->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        // Create PO for org2
        $po2 = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($org2)
            ->create();

        $po2->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $response = $this->get(route('web.approvals.index'));
        $response->assertStatus(200);

        $approvals = $response->viewData('pendingApprovals');
        $poIds = $approvals->pluck('id')->toArray();

        // Approvers can see POs from all organizations (centralized approval system)
        $this->assertContains($po1->id, $poIds);
        $this->assertContains($po2->id, $poIds);
    }

    public function test_super_admin_can_see_all_organization_approvals(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $user = $this->actingAsSuperAdmin();

        // Create POs for both organizations
        $po1 = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($org1)
            ->create();

        $po1->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $po2 = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($org2)
            ->create();

        $po2->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $response = $this->get(route('web.approvals.index'));
        $response->assertStatus(200);

        $approvals = $response->viewData('pendingApprovals');
        $poIds = $approvals->pluck('id')->toArray();

        $this->assertContains($po1->id, $poIds);
        $this->assertContains($po2->id, $poIds);
    }

    // -----------------------------------------------------------------------
    // VALIDATION TESTS
    // -----------------------------------------------------------------------

    public function test_approval_process_validates_required_fields(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($creator)
            ->create();

        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        // Missing level
        $response = $this->post(route('web.approvals.process', $po), [
            'decision' => 'approved'
        ]);
        $response->assertSessionHasErrors(['level']);

        // Missing decision
        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD
        ]);
        $response->assertSessionHasErrors(['decision']);

        // Invalid level
        $response = $this->post(route('web.approvals.process', $po), [
            'level' => 99,
            'decision' => 'approved'
        ]);
        $response->assertSessionHasErrors(['level']);

        // Invalid decision
        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'invalid'
        ]);
        $response->assertSessionHasErrors(['decision']);
    }

    public function test_cannot_process_already_processed_approval(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($creator)
            ->create();

        // Create already processed approval
        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_APPROVED,
            'approver_id' => $approver->id,
            'actioned_at' => now()
        ]);

        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'approved',
            'notes' => 'Trying to re-approve'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // -----------------------------------------------------------------------
    // AUDIT TRAIL TESTS
    // -----------------------------------------------------------------------

    public function test_approval_actions_are_audited(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($creator)
            ->create();

        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'approved',
            'notes' => 'Approved for testing'
        ]);

        $response->assertRedirect(route('web.approvals.index'));

        // Verify audit log entries were created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'po.approval.approved',
            'entity_type' => PurchaseOrder::class,
            'entity_id' => $po->id
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'po.approved',
            'entity_type' => PurchaseOrder::class,
            'entity_id' => $po->id
        ]);
    }

    // -----------------------------------------------------------------------
    // NOTIFICATION TESTS
    // -----------------------------------------------------------------------

    public function test_approval_decision_sends_notifications(): void
    {
        $organization = Organization::factory()->create();
        $creator = $this->createHealthcareUser($organization);
        $approver = $this->actingAsApprover($organization);

        $po = PurchaseOrder::factory()
            ->underReview()
            ->forOrganization($organization)
            ->createdBy($creator)
            ->create();

        $po->approvals()->create([
            'level' => Approval::LEVEL_STANDARD,
            'status' => Approval::STATUS_PENDING
        ]);

        // Clear any existing notifications
        \Illuminate\Support\Facades\Notification::fake();

        $response = $this->post(route('web.approvals.process', $po), [
            'level' => Approval::LEVEL_STANDARD,
            'decision' => 'approved',
            'notes' => 'Approved for testing'
        ]);

        $response->assertRedirect(route('web.approvals.index'));

        // Verify notification was sent to creator
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $creator,
            \App\Notifications\POApprovalDecisionNotification::class
        );
    }

    // -----------------------------------------------------------------------
    // PAGINATION TESTS
    // -----------------------------------------------------------------------

    public function test_approval_index_pagination(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsApprover($organization);

        // Create more than 10 POs (default pagination limit)
        for ($i = 0; $i < 15; $i++) {
            $po = PurchaseOrder::factory()
                ->underReview()
                ->forOrganization($organization)
                ->create();

            $po->approvals()->create([
                'level' => Approval::LEVEL_STANDARD,
                'status' => Approval::STATUS_PENDING
            ]);
        }

        $response = $this->get(route('web.approvals.index'));
        $response->assertStatus(200);

        $approvals = $response->viewData('pendingApprovals');
        $this->assertEquals(10, $approvals->count()); // Should be paginated
        $this->assertTrue($approvals->hasPages());
    }
}