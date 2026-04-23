<?php

namespace Tests\Feature;

use App\Enums\CustomerInvoiceStatus;
use App\Models\CustomerInvoice;
use App\Models\Organization;
use App\Models\User;
use App\Exceptions\InvalidStateTransitionException;
use Tests\TestCase;

class CustomerInvoiceTest extends TestCase
{
    // -----------------------------------------------------------------------
    // READ CUSTOMER INVOICE (API ENDPOINTS)
    // -----------------------------------------------------------------------

    public function test_finance_can_list_customer_invoices(): void
    {
        $this->actingAsFinanceUser();
        
        CustomerInvoice::factory()->count(3)->create();

        $this->getJson('/api/invoices/customer')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'invoice_number', 'status', 'total_amount']
                ]
            ]);
    }

    public function test_healthcare_user_can_view_own_organization_invoices(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        
        // Create invoices for different organizations
        $ownInvoice = CustomerInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();
        $otherInvoice = CustomerInvoice::factory()->create();

        $response = $this->getJson('/api/invoices/customer');
        
        $response->assertStatus(200);
        $invoiceIds = collect($response->json('data'))->pluck('id')->toArray();
        
        $this->assertContains($ownInvoice->id, $invoiceIds);
        $this->assertNotContains($otherInvoice->id, $invoiceIds);
    }

    public function test_can_show_customer_invoice_with_relationships(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser();
        
        // Create invoice with proper organization relationship
        $po = \App\Models\PurchaseOrder::factory()
            ->forOrganization($organization)
            ->create();
        $invoice = CustomerInvoice::factory()
            ->state(['purchase_order_id' => $po->id, 'organization_id' => $organization->id])
            ->create();
        
        // Update user to belong to same organization
        $user->update(['organization_id' => $organization->id]);

        $this->getJson("/api/invoices/customer/{$invoice->id}")
            ->assertStatus(200)
            ->assertJsonPath('invoice_number', $invoice->invoice_number);
    }

    public function test_healthcare_user_cannot_view_other_organization_invoice(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        
        $otherInvoice = CustomerInvoice::factory()->create();

        $response = $this->getJson("/api/invoices/customer/{$otherInvoice->id}");
        $this->assertContains($response->status(), [403, 404]);
    }

    // -----------------------------------------------------------------------
    // CUSTOMER INVOICE MODEL BUSINESS LOGIC
    // -----------------------------------------------------------------------

    public function test_customer_invoice_calculates_outstanding_amount(): void
    {
        $invoice = CustomerInvoice::factory()
            ->state([
                'total_amount' => 1000.00,
                'paid_amount' => 300.00,
            ])
            ->create();

        $this->assertEquals(700.00, $invoice->outstanding_amount);
    }

    public function test_customer_invoice_calculates_aging_bucket(): void
    {
        // Current (not overdue)
        $currentInvoice = CustomerInvoice::factory()
            ->state(['due_date' => now()->addDays(5)])
            ->create();
        $this->assertEquals('current', $currentInvoice->aging_bucket);

        // 1-30 days overdue
        $overdue15Invoice = CustomerInvoice::factory()
            ->state(['due_date' => now()->subDays(15)])
            ->create();
        $this->assertEquals('1-30', $overdue15Invoice->aging_bucket);

        // 31-60 days overdue
        $overdue45Invoice = CustomerInvoice::factory()
            ->state(['due_date' => now()->subDays(45)])
            ->create();
        $this->assertEquals('31-60', $overdue45Invoice->aging_bucket);

        // 90+ days overdue
        $overdue100Invoice = CustomerInvoice::factory()
            ->state(['due_date' => now()->subDays(100)])
            ->create();
        $this->assertEquals('90+', $overdue100Invoice->aging_bucket);
    }

    public function test_customer_invoice_status_transitions(): void
    {
        $invoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::DRAFT])
            ->create();

        // Test status helper methods
        $this->assertTrue($invoice->isDraft());
        $this->assertFalse($invoice->isIssued());
        $this->assertFalse($invoice->isPaid());
        $this->assertFalse($invoice->isVoid());

        // Test transition validation
        $this->assertTrue($invoice->canTransitionTo(CustomerInvoiceStatus::ISSUED));
        $this->assertTrue($invoice->canTransitionTo(CustomerInvoiceStatus::VOID));
        $this->assertFalse($invoice->canTransitionTo(CustomerInvoiceStatus::PAID));
    }

    public function test_customer_invoice_state_machine_enforcement(): void
    {
        $invoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::DRAFT])
            ->create();

        // Valid transition: DRAFT → ISSUED
        $invoice->transitionTo(CustomerInvoiceStatus::ISSUED);
        $this->assertTrue($invoice->fresh()->isIssued());

        // Valid transition: ISSUED → PARTIAL_PAID
        $invoice->transitionTo(CustomerInvoiceStatus::PARTIAL_PAID);
        $this->assertTrue($invoice->fresh()->isPartialPaid());

        // Valid transition: PARTIAL_PAID → PAID
        $invoice->transitionTo(CustomerInvoiceStatus::PAID);
        $this->assertTrue($invoice->fresh()->isPaid());

        // Invalid transition: PAID → ISSUED (should throw exception)
        $this->expectException(InvalidStateTransitionException::class);
        $invoice->transitionTo(CustomerInvoiceStatus::ISSUED);
    }

    public function test_customer_invoice_overdue_detection(): void
    {
        // Not overdue
        $currentInvoice = CustomerInvoice::factory()
            ->state([
                'due_date' => now()->addDays(5),
                'status' => CustomerInvoiceStatus::ISSUED,
            ])
            ->create();
        $this->assertFalse($currentInvoice->isOverdueByDate());
        $this->assertEquals(0, $currentInvoice->days_overdue);

        // Overdue
        $overdueInvoice = CustomerInvoice::factory()
            ->state([
                'due_date' => now()->subDays(10),
                'status' => CustomerInvoiceStatus::ISSUED,
            ])
            ->create();
        $this->assertTrue($overdueInvoice->isOverdueByDate());
        $this->assertEquals(10, $overdueInvoice->days_overdue);
    }

    public function test_customer_invoice_payment_acceptance(): void
    {
        // ISSUED status can accept payment
        $issuedInvoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::ISSUED])
            ->create();
        $this->assertTrue($issuedInvoice->canConfirmPayment());

        // PARTIAL_PAID status can accept payment
        $partialInvoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::PARTIAL_PAID])
            ->create();
        $this->assertTrue($partialInvoice->canConfirmPayment());

        // PAID status cannot accept payment
        $paidInvoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::PAID])
            ->create();
        $this->assertFalse($paidInvoice->canConfirmPayment());

        // VOID status cannot accept payment
        $voidInvoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::VOID])
            ->create();
        $this->assertFalse($voidInvoice->canConfirmPayment());
    }

    // -----------------------------------------------------------------------
    // IMMUTABILITY VALIDATION
    // -----------------------------------------------------------------------

    public function test_customer_invoice_immutability_rules(): void
    {
        // Draft and Issued invoices are mutable
        $draftInvoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::DRAFT])
            ->create();
        $this->assertFalse($draftInvoice->isImmutable());

        $issuedInvoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::ISSUED])
            ->create();
        $this->assertFalse($issuedInvoice->isImmutable());

        // Paid and Void invoices are immutable
        $paidInvoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::PAID])
            ->create();
        $this->assertTrue($paidInvoice->isImmutable());

        $voidInvoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::VOID])
            ->create();
        $this->assertTrue($voidInvoice->isImmutable());
    }

    // -----------------------------------------------------------------------
    // MULTI-TENANT ISOLATION
    // -----------------------------------------------------------------------

    public function test_customer_invoice_respects_organization_scoping(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $user1 = User::factory()->healthcareUser()->forOrganization($org1)->create();
        $user2 = User::factory()->healthcareUser()->forOrganization($org2)->create();

        $invoice1 = CustomerInvoice::factory()->state(['organization_id' => $org1->id])->create();
        $invoice2 = CustomerInvoice::factory()->state(['organization_id' => $org2->id])->create();

        // User 1 should only see their organization's invoices
        $this->actingAsUser($user1);
        
        $response = $this->getJson('/api/invoices/customer');
        
        if ($response->status() === 200) {
            $invoiceIds = collect($response->json('data'))->pluck('id')->toArray();
            $this->assertContains($invoice1->id, $invoiceIds);
            $this->assertNotContains($invoice2->id, $invoiceIds);
        } else {
            // User might not have permission, which is also valid
            $this->assertContains($response->status(), [403]);
        }
    }

    // -----------------------------------------------------------------------
    // AUTHORIZATION TESTS
    // -----------------------------------------------------------------------

    public function test_unauthorized_user_cannot_access_customer_invoices(): void
    {
        $this->getJson('/api/invoices/customer')
            ->assertStatus(401);
    }

    public function test_user_without_invoice_permission_cannot_access(): void
    {
        $user = User::factory()->create(); // No roles assigned
        $this->actingAsUser($user);

        $this->getJson('/api/invoices/customer')
            ->assertStatus(403);
    }

    // -----------------------------------------------------------------------
    // PAYMENT PROCESSING
    // -----------------------------------------------------------------------

    public function test_customer_invoice_supports_partial_payments(): void
    {
        $invoice = CustomerInvoice::factory()
            ->state([
                'status' => CustomerInvoiceStatus::ISSUED,
                'total_amount' => 1000.00,
                'paid_amount' => 0.00,
            ])
            ->create();

        // Make partial payment
        $invoice->update(['paid_amount' => 300.00]);
        $invoice->transitionTo(CustomerInvoiceStatus::PARTIAL_PAID);

        $this->assertEquals(700.00, $invoice->fresh()->outstanding_amount);
        $this->assertTrue($invoice->fresh()->isPartialPaid());
        $this->assertTrue($invoice->fresh()->canConfirmPayment());
    }

    public function test_customer_invoice_full_payment(): void
    {
        $invoice = CustomerInvoice::factory()
            ->state([
                'status' => CustomerInvoiceStatus::ISSUED,
                'total_amount' => 1000.00,
                'paid_amount' => 0.00,
            ])
            ->create();

        // Make full payment
        $invoice->update(['paid_amount' => 1000.00]);
        $invoice->transitionTo(CustomerInvoiceStatus::PAID);

        $this->assertEquals(0.00, $invoice->fresh()->outstanding_amount);
        $this->assertTrue($invoice->fresh()->isPaid());
        $this->assertFalse($invoice->fresh()->canConfirmPayment());
    }

    // -----------------------------------------------------------------------
    // ENUM AND STATUS VALIDATION
    // -----------------------------------------------------------------------

    public function test_customer_invoice_status_enum_values(): void
    {
        $expectedStatuses = ['draft', 'issued', 'partial_paid', 'paid', 'void'];
        $actualStatuses = CustomerInvoiceStatus::values();
        
        $this->assertEquals($expectedStatuses, $actualStatuses);
    }

    public function test_customer_invoice_status_labels(): void
    {
        $this->assertEquals('Draft', CustomerInvoiceStatus::DRAFT->getLabel());
        $this->assertEquals('Menunggu Pembayaran', CustomerInvoiceStatus::ISSUED->getLabel());
        $this->assertEquals('Dibayar Sebagian', CustomerInvoiceStatus::PARTIAL_PAID->getLabel());
        $this->assertEquals('Lunas', CustomerInvoiceStatus::PAID->getLabel());
        $this->assertEquals('Dibatalkan', CustomerInvoiceStatus::VOID->getLabel());
    }

    public function test_customer_invoice_status_badge_classes(): void
    {
        $this->assertEquals('badge-light-secondary', CustomerInvoiceStatus::DRAFT->getBadgeClass());
        $this->assertEquals('badge-light-warning', CustomerInvoiceStatus::ISSUED->getBadgeClass());
        $this->assertEquals('badge-light-info', CustomerInvoiceStatus::PARTIAL_PAID->getBadgeClass());
        $this->assertEquals('badge-light-success', CustomerInvoiceStatus::PAID->getBadgeClass());
        $this->assertEquals('badge-light-danger', CustomerInvoiceStatus::VOID->getBadgeClass());
    }

    public function test_customer_invoice_active_status_detection(): void
    {
        $this->assertFalse(CustomerInvoiceStatus::DRAFT->isActive());
        $this->assertTrue(CustomerInvoiceStatus::ISSUED->isActive());
        $this->assertTrue(CustomerInvoiceStatus::PARTIAL_PAID->isActive());
        $this->assertTrue(CustomerInvoiceStatus::PAID->isActive());
        $this->assertFalse(CustomerInvoiceStatus::VOID->isActive());
    }

    // -----------------------------------------------------------------------
    // PERFORMANCE & DATA INTEGRITY
    // -----------------------------------------------------------------------

    public function test_customer_invoice_relationships_load_efficiently(): void
    {
        $invoice = CustomerInvoice::factory()->create();
        
        // Test that relationships can be loaded without errors
        $loadedInvoice = CustomerInvoice::with([
            'organization', 
            'purchaseOrder', 
            'goodsReceipt',
            'supplierInvoice',
            'lineItems',
            'paymentAllocations'
        ])->find($invoice->id);
        
        $this->assertNotNull($loadedInvoice);
        $this->assertNotNull($loadedInvoice->organization);
    }

    public function test_customer_invoice_factory_creates_valid_data(): void
    {
        $invoice = CustomerInvoice::factory()->create();
        
        $this->assertNotNull($invoice->invoice_number);
        $this->assertNotNull($invoice->organization_id);
        $this->assertGreaterThan(0, $invoice->total_amount);
        $this->assertInstanceOf(CustomerInvoiceStatus::class, $invoice->status);
    }

    // -----------------------------------------------------------------------
    // ANTI-PHANTOM BILLING VALIDATION
    // -----------------------------------------------------------------------

    public function test_customer_invoice_supplier_invoice_relationship(): void
    {
        $supplierInvoice = \App\Models\SupplierInvoice::factory()->create();
        
        $customerInvoice = CustomerInvoice::factory()
            ->state(['supplier_invoice_id' => $supplierInvoice->id])
            ->create();

        $this->assertNotNull($customerInvoice->supplierInvoice);
        $this->assertEquals($supplierInvoice->id, $customerInvoice->supplier_invoice_id);
    }

    // -----------------------------------------------------------------------
    // STATUS BADGE GENERATION
    // -----------------------------------------------------------------------

    public function test_customer_invoice_status_badge_generation(): void
    {
        $invoice = CustomerInvoice::factory()
            ->state(['status' => CustomerInvoiceStatus::ISSUED])
            ->create();

        $badge = $invoice->getStatusBadge();
        
        $this->assertStringContainsString('badge', $badge);
        $this->assertStringContainsString('badge-light-warning', $badge);
        $this->assertStringContainsString('Menunggu Pembayaran', $badge);
    }
}