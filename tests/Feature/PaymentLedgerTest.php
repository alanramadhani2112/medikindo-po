<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\CustomerInvoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentLedgerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    // -----------------------------------------------------------------------
    // PAYMENT LEDGER INDEX TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_payment_ledger_index(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create some payments for testing
        $incomingPayment = Payment::factory()
            ->incoming()
            ->state(['organization_id' => $organization->id, 'amount' => 1000000])
            ->create();

        $outgoingPayment = Payment::factory()
            ->outgoing()
            ->state(['organization_id' => $organization->id, 'amount' => 500000])
            ->create();

        $response = $this->get(route('web.payments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('payments.index');
        $response->assertViewHas(['payments', 'totalIn', 'totalOut', 'counts', 'tab']);
        
        // Check if payments are displayed
        $response->assertSee($incomingPayment->payment_number);
        $response->assertSee($outgoingPayment->payment_number);
        
        // Check summary cards
        $response->assertSee('Total Kas Masuk');
        $response->assertSee('Total Kas Keluar');
        $response->assertSee('Saldo Netto');
    }

    public function test_healthcare_user_cannot_access_payment_ledger(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $response = $this->get(route('web.payments.index'));

        $response->assertStatus(403);
    }

    public function test_can_filter_payments_by_type(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $incomingPayment = Payment::factory()
            ->incoming()
            ->state(['organization_id' => $organization->id])
            ->create();

        $outgoingPayment = Payment::factory()
            ->outgoing()
            ->state(['organization_id' => $organization->id])
            ->create();

        // Test incoming filter
        $response = $this->get(route('web.payments.index', ['type' => 'incoming']));
        $response->assertStatus(200);
        $payments = $response->viewData('payments');
        $this->assertTrue($payments->contains($incomingPayment));
        $this->assertFalse($payments->contains($outgoingPayment));

        // Test outgoing filter
        $response = $this->get(route('web.payments.index', ['type' => 'outgoing']));
        $response->assertStatus(200);
        $payments = $response->viewData('payments');
        $this->assertFalse($payments->contains($incomingPayment));
        $this->assertTrue($payments->contains($outgoingPayment));
    }

    public function test_can_filter_payments_by_tab(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $incomingPayment = Payment::factory()
            ->incoming()
            ->state(['organization_id' => $organization->id])
            ->create();

        $pendingPayment = Payment::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => 'pending'
            ])
            ->create();

        // Test incoming tab
        $response = $this->get(route('web.payments.index', ['tab' => 'incoming']));
        $response->assertStatus(200);
        $payments = $response->viewData('payments');
        $this->assertTrue($payments->contains($incomingPayment));

        // Test pending tab
        $response = $this->get(route('web.payments.index', ['tab' => 'pending']));
        $response->assertStatus(200);
        $payments = $response->viewData('payments');
        $this->assertTrue($payments->contains($pendingPayment));
    }

    public function test_can_search_payments(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $payment = Payment::factory()
            ->state([
                'organization_id' => $organization->id,
                'payment_number' => 'PAY-TEST-123',
                'reference' => 'REF-SEARCH-456'
            ])
            ->create();

        // Search by payment number
        $response = $this->get(route('web.payments.index', ['search' => 'PAY-TEST']));
        $response->assertStatus(200);
        $payments = $response->viewData('payments');
        $this->assertTrue($payments->contains($payment));

        // Search by reference
        $response = $this->get(route('web.payments.index', ['search' => 'REF-SEARCH']));
        $response->assertStatus(200);
        $payments = $response->viewData('payments');
        $this->assertTrue($payments->contains($payment));
    }

    public function test_calculates_totals_correctly(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create incoming payments
        Payment::factory()
            ->incoming()
            ->state([
                'organization_id' => $organization->id,
                'amount' => 1000000,
                'status' => 'completed'
            ])
            ->create();

        Payment::factory()
            ->incoming()
            ->state([
                'organization_id' => $organization->id,
                'amount' => 500000,
                'status' => 'completed'
            ])
            ->create();

        // Create outgoing payments
        Payment::factory()
            ->outgoing()
            ->state([
                'organization_id' => $organization->id,
                'amount' => 300000,
                'status' => 'completed'
            ])
            ->create();

        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(200);

        $totalIn = $response->viewData('totalIn');
        $totalOut = $response->viewData('totalOut');

        $this->assertEquals(1500000, $totalIn); // 1000000 + 500000
        $this->assertEquals(300000, $totalOut);
    }

    // -----------------------------------------------------------------------
    // PAYMENT DETAIL TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_payment_detail(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()->create();
        
        $payment = Payment::factory()
            ->state([
                'organization_id' => $organization->id,
                'bank_account_id' => $bankAccount->id,
                'payment_method' => 'Bank Transfer',
                'reference' => 'TRF123456',
                'description' => 'Test payment description'
            ])
            ->create();

        $response = $this->get(route('web.payments.show', $payment));

        $response->assertStatus(200);
        $response->assertViewIs('payments.show');
        $response->assertViewHas('payment');
        
        // Check payment details are displayed
        $response->assertSee($payment->payment_number);
        $response->assertSee($payment->reference);
        $response->assertSee($payment->description);
        $response->assertSee($bankAccount->bank_name);
    }

    public function test_cannot_view_other_organization_payment_detail(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $user = $this->actingAsFinanceUser($org1);
        
        $payment = Payment::factory()
            ->state(['organization_id' => $org2->id])
            ->create();

        $response = $this->get(route('web.payments.show', $payment));
        
        // Should be handled by policy/middleware - might return 403 or redirect
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    // -----------------------------------------------------------------------
    // CREATE INCOMING PAYMENT TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_create_incoming_payment_form(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => \App\Enums\CustomerInvoiceStatus::ISSUED,
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get(route('web.payments.create.incoming'));

        $response->assertStatus(200);
        $response->assertViewIs('payments.create_incoming');
        $response->assertViewHas(['invoices', 'bankAccounts']);
        
        // Check form elements
        $response->assertSee('Manual Payment Entry');
        $response->assertSee('Pilih Invoice AR');
        $response->assertSee($invoice->invoice_number);
    }

    public function test_can_view_create_incoming_payment_with_specific_invoice(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => \App\Enums\CustomerInvoiceStatus::ISSUED,
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get(route('web.payments.create.incoming', ['invoice_id' => $invoice->id]));

        $response->assertStatus(200);
        $response->assertViewHas('invoice', $invoice);
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('Invoice terkunci');
    }

    public function test_can_create_incoming_payment(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $bankAccount = BankAccount::factory()->create();
        
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => \App\Enums\CustomerInvoiceStatus::ISSUED,
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $file = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->post(route('web.payments.store.incoming'), [
            'customer_invoice_id' => $invoice->id,
            'amount' => 1000000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'bank_account_id' => $bankAccount->id,
            'sender_bank_name' => 'BCA',
            'sender_account_number' => '1234567890',
            'reference' => 'TRF123456',
            'notes' => 'Test payment',
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect(route('web.payments.index'));
        $response->assertSessionHas('success');

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'type' => 'incoming',
            'amount' => 1000000,
            'payment_method' => 'Bank Transfer',
            'reference' => 'TRF123456'
        ]);

        // Verify payment allocation was created
        $payment = Payment::where('type', 'incoming')->where('amount', 1000000)->first();
        $this->assertDatabaseHas('payment_allocations', [
            'payment_id' => $payment->id,
            'customer_invoice_id' => $invoice->id,
            'allocated_amount' => 1000000
        ]);
    }

    // -----------------------------------------------------------------------
    // CREATE OUTGOING PAYMENT TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_create_outgoing_payment_form(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $supplierInvoice = SupplierInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => \App\Enums\SupplierInvoiceStatus::VERIFIED,
                'total_amount' => 800000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get(route('web.payments.create.outgoing'));

        $response->assertStatus(200);
        $response->assertViewIs('payments.create_outgoing');
        $response->assertViewHas(['invoices', 'bankAccounts']);
        
        // Check form elements
        $response->assertSee('Kirim Pembayaran');
        $response->assertSee($supplierInvoice->invoice_number);
    }

    public function test_can_create_outgoing_payment(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $bankAccount = BankAccount::factory()->create();
        
        // Create PO and related invoices for cashflow validation
        $po = \App\Models\PurchaseOrder::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $supplierInvoice = SupplierInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'status' => \App\Enums\SupplierInvoiceStatus::VERIFIED,
                'total_amount' => 800000,
                'paid_amount' => 0
            ])
            ->create();

        // Create customer invoice with sufficient payment
        $customerInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'goods_receipt_id' => $supplierInvoice->goods_receipt_id,
                'paid_amount' => 1000000 // Enough to cover supplier payment
            ])
            ->create();

        $response = $this->post(route('web.payments.store.outgoing'), [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 800000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'bank_account_id' => $bankAccount->id,
            'reference' => 'PAY-SUP-001',
            'description' => 'Payment to supplier'
        ]);

        $response->assertRedirect(route('web.payments.index'));
        $response->assertSessionHas('success');

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'type' => 'outgoing',
            'amount' => 800000,
            'payment_method' => 'Bank Transfer',
            'reference' => 'PAY-SUP-001'
        ]);
    }

    // -----------------------------------------------------------------------
    // MULTI-TENANT SECURITY TESTS
    // -----------------------------------------------------------------------

    public function test_payment_ledger_multi_tenant_isolation(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $user1 = $this->actingAsFinanceUser($org1);
        
        $payment1 = Payment::factory()
            ->state(['organization_id' => $org1->id])
            ->create();
            
        $payment2 = Payment::factory()
            ->state(['organization_id' => $org2->id])
            ->create();

        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(200);
        
        $payments = $response->viewData('payments');
        $paymentIds = $payments->pluck('id')->toArray();
        
        $this->assertContains($payment1->id, $paymentIds);
        $this->assertNotContains($payment2->id, $paymentIds);
    }

    public function test_super_admin_can_see_all_organization_payments(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $user = $this->actingAsSuperAdmin();
        
        $payment1 = Payment::factory()
            ->state(['organization_id' => $org1->id])
            ->create();
            
        $payment2 = Payment::factory()
            ->state(['organization_id' => $org2->id])
            ->create();

        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(200);
        
        $payments = $response->viewData('payments');
        $paymentIds = $payments->pluck('id')->toArray();
        
        $this->assertContains($payment1->id, $paymentIds);
        $this->assertContains($payment2->id, $paymentIds);
    }

    // -----------------------------------------------------------------------
    // PAYMENT ALLOCATION DISPLAY TESTS
    // -----------------------------------------------------------------------

    public function test_payment_detail_shows_allocations(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $payment = Payment::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $invoice = CustomerInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        PaymentAllocation::factory()
            ->forPayment($payment)
            ->forCustomerInvoice($invoice)
            ->withAmount(500000)
            ->create();

        $response = $this->get(route('web.payments.show', $payment));

        $response->assertStatus(200);
        $response->assertSee($invoice->invoice_number);
        $response->assertSee('Rp 500.000');
    }

    // -----------------------------------------------------------------------
    // PAYMENT LEDGER SUMMARY TESTS
    // -----------------------------------------------------------------------

    public function test_payment_ledger_summary_calculations(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create various payments
        Payment::factory()
            ->incoming()
            ->state([
                'organization_id' => $organization->id,
                'amount' => 2000000,
                'status' => 'completed'
            ])
            ->create();

        Payment::factory()
            ->outgoing()
            ->state([
                'organization_id' => $organization->id,
                'amount' => 800000,
                'status' => 'completed'
            ])
            ->create();

        Payment::factory()
            ->incoming()
            ->state([
                'organization_id' => $organization->id,
                'amount' => 500000,
                'status' => 'pending' // Should not be included in totals
            ])
            ->create();

        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(200);

        $totalIn = $response->viewData('totalIn');
        $totalOut = $response->viewData('totalOut');

        $this->assertEquals(2000000, $totalIn); // Only completed payments
        $this->assertEquals(800000, $totalOut);
        
        // Check net balance calculation in view
        $response->assertSee('Rp 1.200.000'); // Net balance: 2000000 - 800000
    }

    // -----------------------------------------------------------------------
    // AUTHORIZATION TESTS
    // -----------------------------------------------------------------------

    public function test_only_finance_users_can_access_payment_ledger(): void
    {
        $organization = Organization::factory()->create();

        // Test Healthcare User (should be denied)
        $healthcareUser = $this->actingAsHealthcareUser($organization);
        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(403);

        // Test Finance User (should be allowed)
        $financeUser = $this->actingAsFinanceUser($organization);
        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(200);

        // Test Super Admin (should be allowed)
        $superAdmin = $this->actingAsSuperAdmin();
        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(200);
    }

    public function test_only_authorized_users_can_create_payments(): void
    {
        $organization = Organization::factory()->create();

        // Test Healthcare User (should be denied)
        $healthcareUser = $this->actingAsHealthcareUser($organization);
        $response = $this->get(route('web.payments.create.incoming'));
        $response->assertStatus(403);

        // Test Finance User (should be allowed)
        $financeUser = $this->actingAsFinanceUser($organization);
        $response = $this->get(route('web.payments.create.incoming'));
        $response->assertStatus(200);
    }

    // -----------------------------------------------------------------------
    // PAGINATION AND PERFORMANCE TESTS
    // -----------------------------------------------------------------------

    public function test_payment_ledger_pagination(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create more than 15 payments (default pagination limit)
        Payment::factory()
            ->count(20)
            ->state(['organization_id' => $organization->id])
            ->create();

        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(200);

        $payments = $response->viewData('payments');
        $this->assertEquals(15, $payments->count()); // Should be paginated
        $this->assertTrue($payments->hasPages());
    }

    public function test_payment_ledger_loads_relationships_efficiently(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $payment = Payment::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $response = $this->get(route('web.payments.index'));
        $response->assertStatus(200);

        // Verify that relationships are loaded (no N+1 queries)
        $payments = $response->viewData('payments');
        $firstPayment = $payments->first();
        
        if ($firstPayment) {
            // These should be loaded without additional queries
            $this->assertTrue($firstPayment->relationLoaded('organization'));
            $this->assertTrue($firstPayment->relationLoaded('allocations'));
        }
    }
}