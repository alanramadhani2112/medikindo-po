<?php

namespace Tests\Feature;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\SupplierInvoiceStatus;
use App\Models\BankAccount;
use App\Models\CustomerInvoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManualPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    // -----------------------------------------------------------------------
    // MANUAL INCOMING PAYMENT TESTS (Web Interface)
    // -----------------------------------------------------------------------

    public function test_can_access_manual_incoming_payment_form(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $response = $this->get('/payments/incoming');

        $response->assertStatus(200);
        $response->assertSee('Manual Payment Entry');
        $response->assertSee('Pilih Invoice AR');
        $response->assertSee('Metode Pembayaran');
    }

    public function test_healthcare_user_cannot_access_manual_payment_form(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $response = $this->get('/payments/incoming');

        $response->assertStatus(403);
    }

    public function test_can_submit_manual_incoming_payment_full(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()
            ->state(['current_balance' => 1000000])
            ->create();

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 500000,
                'paid_amount' => 0,
                'status' => CustomerInvoiceStatus::ISSUED
            ])
            ->create();

        $file = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 500000,
            'payment_type' => 'full',
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'sender_bank_name' => 'Bank Central Asia',
            'sender_account_number' => '1234567890',
            'bank_account_id' => $bankAccount->id,
            'reference' => 'TRF123456',
            'notes' => 'Manual full payment',
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'type' => 'incoming',
            'amount' => 500000,
            'payment_method' => 'Bank Transfer',
            'status' => 'completed'
        ]);

        // Verify invoice status updated
        $invoice->refresh();
        $this->assertEquals(500000, $invoice->paid_amount);
        $this->assertEquals(CustomerInvoiceStatus::PAID, $invoice->status);
    }

    public function test_can_submit_manual_incoming_payment_partial(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()->create();

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 1000000,
                'paid_amount' => 0,
                'status' => CustomerInvoiceStatus::ISSUED
            ])
            ->create();

        $file = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 600000,
            'payment_type' => 'partial',
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Cash',
            'receipt_number' => 'KWT-001234',
            'bank_account_id' => $bankAccount->id,
            'notes' => 'Partial cash payment',
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'type' => 'incoming',
            'amount' => 600000,
            'payment_method' => 'Cash'
        ]);

        // Verify invoice status updated to partial
        $invoice->refresh();
        $this->assertEquals(600000, $invoice->paid_amount);
        $this->assertEquals(CustomerInvoiceStatus::PARTIAL_PAID, $invoice->status);
    }

    public function test_can_submit_manual_incoming_payment_with_giro(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()->create();

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 750000,
                'paid_amount' => 0,
                'status' => CustomerInvoiceStatus::ISSUED
            ])
            ->create();

        $file = UploadedFile::fake()->image('giro_photo.jpg');

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 750000,
            'payment_type' => 'full',
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Giro/Cek',
            'giro_number' => 'GR123456',
            'giro_due_date' => now()->addDays(30)->format('Y-m-d'),
            'issuing_bank' => 'Bank Mandiri',
            'giro_reference' => 'GIRO-REF-001',
            'bank_account_id' => $bankAccount->id,
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify payment with giro details
        $this->assertDatabaseHas('payments', [
            'type' => 'incoming',
            'amount' => 750000,
            'payment_method' => 'Giro/Cek',
            'giro_number' => 'GR123456',
            'issuing_bank' => 'Bank Mandiri'
        ]);

        // Invoice should be marked as paid (trust-based for giro)
        $invoice->refresh();
        $this->assertEquals(CustomerInvoiceStatus::PAID, $invoice->status);
    }

    public function test_manual_incoming_payment_validation_errors(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Test missing required fields
        $response = $this->post('/payments/incoming', []);

        $response->assertSessionHasErrors([
            'customer_invoice_id',
            'amount',
            'payment_method',
            'bank_account_id',
            'payment_proof_file'
        ]);

        // Test invalid amount
        $invoice = CustomerInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 0,
            'payment_method' => 'Bank Transfer'
        ]);

        $response->assertSessionHasErrors(['amount']);

        // Test invalid payment method
        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 100000,
            'payment_method' => 'Invalid Method'
        ]);

        $response->assertSessionHasErrors(['payment_method']);
    }

    public function test_manual_incoming_payment_conditional_validation(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $invoice = CustomerInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        // Test Bank Transfer requires sender details
        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 100000,
            'payment_method' => 'Bank Transfer',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => UploadedFile::fake()->image('proof.jpg')
        ]);

        $response->assertSessionHasErrors([
            'sender_bank_name',
            'sender_account_number',
            'reference'
        ]);

        // Test Giro/Cek requires giro details
        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 100000,
            'payment_method' => 'Giro/Cek',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => UploadedFile::fake()->image('proof.jpg')
        ]);

        $response->assertSessionHasErrors([
            'giro_number',
            'giro_due_date',
            'issuing_bank',
            'giro_reference'
        ]);

        // Test Cash requires receipt number
        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 100000,
            'payment_method' => 'Cash',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => UploadedFile::fake()->image('proof.jpg')
        ]);

        $response->assertSessionHasErrors(['receipt_number']);
    }

    // -----------------------------------------------------------------------
    // MANUAL OUTGOING PAYMENT TESTS (Web Interface)
    // -----------------------------------------------------------------------

    public function test_can_access_manual_outgoing_payment_form(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $response = $this->get('/payments/outgoing');

        $response->assertStatus(200);
        $response->assertSee('Kirim Pembayaran ke Supplier');
        $response->assertSee('Pilih Invoice Supplier');
        $response->assertSee('Formulir Pengeluaran Kas');
    }

    public function test_can_submit_manual_outgoing_payment_full(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()
            ->state(['current_balance' => 2000000])
            ->create();

        // Create PO and related invoices
        $po = PurchaseOrder::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $supplierInvoice = SupplierInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'total_amount' => 800000,
                'paid_amount' => 0,
                'status' => SupplierInvoiceStatus::VERIFIED
            ])
            ->create();

        // Create corresponding customer invoice with sufficient payment
        $customerInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'goods_receipt_id' => $supplierInvoice->goods_receipt_id,
                'total_amount' => 1000000,
                'paid_amount' => 1000000,
                'status' => CustomerInvoiceStatus::PAID
            ])
            ->create();

        $response = $this->post('/payments/outgoing', [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 800000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'bank_account_id' => $bankAccount->id,
            'bank_name_manual' => 'Bank Mandiri',
            'reference' => 'PAY-SUP-001',
            'description' => 'Full payment to supplier'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'type' => 'outgoing',
            'amount' => 800000,
            'payment_method' => 'Bank Transfer',
            'status' => 'completed'
        ]);

        // Verify supplier invoice status updated
        $supplierInvoice->refresh();
        $this->assertEquals(800000, $supplierInvoice->paid_amount);
        $this->assertEquals(SupplierInvoiceStatus::PAID, $supplierInvoice->status);
    }

    public function test_can_submit_manual_outgoing_payment_partial(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()->create();

        $po = PurchaseOrder::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $supplierInvoice = SupplierInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'total_amount' => 1000000,
                'paid_amount' => 0,
                'status' => SupplierInvoiceStatus::VERIFIED
            ])
            ->create();

        // Customer paid enough to cover partial supplier payment
        $customerInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'goods_receipt_id' => $supplierInvoice->goods_receipt_id,
                'paid_amount' => 800000,
            ])
            ->create();

        $response = $this->post('/payments/outgoing', [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 600000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Cash',
            'description' => 'Partial cash payment'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify payment was created
        $this->assertDatabaseHas('payments', [
            'type' => 'outgoing',
            'amount' => 600000,
            'payment_method' => 'Cash'
        ]);

        // Verify supplier invoice remains VERIFIED (partial payment)
        $supplierInvoice->refresh();
        $this->assertEquals(600000, $supplierInvoice->paid_amount);
        $this->assertEquals(SupplierInvoiceStatus::VERIFIED, $supplierInvoice->status);
    }

    public function test_manual_outgoing_payment_validation_errors(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Test missing required fields
        $response = $this->post('/payments/outgoing', []);

        $response->assertSessionHasErrors([
            'supplier_invoice_id',
            'amount',
            'payment_method'
        ]);

        // Test invalid amount
        $supplierInvoice = SupplierInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $response = $this->post('/payments/outgoing', [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 0,
            'payment_method' => 'Bank Transfer'
        ]);

        $response->assertSessionHasErrors(['amount']);
    }

    // -----------------------------------------------------------------------
    // MANUAL PAYMENT BUSINESS LOGIC TESTS
    // -----------------------------------------------------------------------

    public function test_cannot_submit_manual_payment_exceeding_outstanding(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 500000,
                'paid_amount' => 200000, // Already paid 200k
                'status' => CustomerInvoiceStatus::PARTIAL_PAID
            ])
            ->create();

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 400000, // Trying to pay 400k when only 300k outstanding
            'payment_method' => 'Bank Transfer',
            'sender_bank_name' => 'BCA',
            'sender_account_number' => '1234567890',
            'reference' => 'TRF123',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('melebihi sisa tagihan', session('error'));
    }

    public function test_cannot_submit_manual_outgoing_payment_without_customer_payment(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $po = PurchaseOrder::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $supplierInvoice = SupplierInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'total_amount' => 800000,
                'status' => SupplierInvoiceStatus::VERIFIED
            ])
            ->create();

        // Customer invoice exists but not paid enough
        $customerInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'goods_receipt_id' => $supplierInvoice->goods_receipt_id,
                'paid_amount' => 500000, // Only 500k paid, trying to pay supplier 800k
            ])
            ->create();

        $response = $this->post('/payments/outgoing', [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 800000,
            'payment_method' => 'Bank Transfer'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('belum membayar cukup', session('error'));
    }

    public function test_cannot_submit_manual_outgoing_payment_for_unverified_invoice(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $supplierInvoice = SupplierInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => SupplierInvoiceStatus::DRAFT // Not verified
            ])
            ->create();

        $response = $this->post('/payments/outgoing', [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 500000,
            'payment_method' => 'Bank Transfer'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('belum diverifikasi', session('error'));
    }

    // -----------------------------------------------------------------------
    // MANUAL PAYMENT FILE UPLOAD TESTS
    // -----------------------------------------------------------------------

    public function test_manual_payment_file_upload_validation(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $invoice = CustomerInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        // Test invalid file type
        $invalidFile = UploadedFile::fake()->create('document.txt', 100);

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 100000,
            'payment_method' => 'Cash',
            'receipt_number' => 'KWT-001',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => $invalidFile
        ]);

        $response->assertSessionHasErrors(['payment_proof_file']);

        // Test file too large (over 5MB)
        $largeFile = UploadedFile::fake()->create('large.pdf', 6000); // 6MB

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 100000,
            'payment_method' => 'Cash',
            'receipt_number' => 'KWT-001',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => $largeFile
        ]);

        $response->assertSessionHasErrors(['payment_proof_file']);
    }

    public function test_manual_payment_file_upload_success(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 100000,
                'paid_amount' => 0
            ])
            ->create();

        $file = UploadedFile::fake()->image('payment_proof.jpg', 800, 600);

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 100000,
            'payment_method' => 'Cash',
            'receipt_number' => 'KWT-001',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify file was stored
        $payment = Payment::where('type', 'incoming')->latest()->first();
        $this->assertNotNull($payment->payment_proof_path);
        Storage::disk('public')->assertExists($payment->payment_proof_path);
    }

    // -----------------------------------------------------------------------
    // MANUAL PAYMENT AUTHORIZATION TESTS
    // -----------------------------------------------------------------------

    public function test_manual_payment_multi_tenant_isolation(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        // Use Healthcare user instead of Finance user for multi-tenant isolation test
        $user = $this->actingAsHealthcareUser($org1);
        
        $invoiceOrg2 = CustomerInvoice::factory()
            ->state(['organization_id' => $org2->id])
            ->create();

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoiceOrg2->id,
            'amount' => 100000,
            'payment_method' => 'Cash',
            'receipt_number' => 'KWT-001',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => $file
        ]);

        // Healthcare users should get 403 when trying to access other org's invoices
        $response->assertStatus(403);
    }

    public function test_manual_payment_admin_pusat_can_access_all_organizations(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $user = $this->actingAsSuperAdmin();
        
        $invoice = CustomerInvoice::factory()
            ->state(['organization_id' => $org2->id])
            ->create();

        $response = $this->get('/payments/incoming');
        $response->assertStatus(200);

        // Admin Pusat should see invoices from all organizations
        $response->assertSee($invoice->invoice_number);
    }

    // -----------------------------------------------------------------------
    // MANUAL PAYMENT INTEGRATION TESTS
    // -----------------------------------------------------------------------

    public function test_manual_payment_updates_bank_account_balance(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()
            ->state(['current_balance' => 1000000])
            ->create();

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 300000,
                'paid_amount' => 0
            ])
            ->create();

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 300000,
            'payment_method' => 'Bank Transfer',
            'sender_bank_name' => 'BCA',
            'sender_account_number' => '1234567890',
            'reference' => 'TRF123',
            'bank_account_id' => $bankAccount->id,
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect();

        // Verify bank account balance increased
        $bankAccount->refresh();
        $this->assertEquals(1300000, $bankAccount->current_balance);
    }

    public function test_manual_payment_creates_payment_allocation(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 200000,
                'paid_amount' => 0
            ])
            ->create();

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 200000,
            'payment_method' => 'Cash',
            'receipt_number' => 'KWT-001',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect();

        // Verify payment allocation was created
        $payment = Payment::where('type', 'incoming')->latest()->first();
        $this->assertCount(1, $payment->allocations);
        
        $allocation = $payment->allocations->first();
        $this->assertEquals($invoice->id, $allocation->customer_invoice_id);
        $this->assertEquals(200000, $allocation->allocated_amount);
    }

    public function test_manual_payment_generates_correct_payment_number(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $invoice = CustomerInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->post('/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 100000,
            'payment_method' => 'Cash',
            'receipt_number' => 'KWT-001',
            'bank_account_id' => BankAccount::factory()->create()->id,
            'payment_proof_file' => $file
        ]);

        $response->assertRedirect();

        // Verify payment number format
        $payment = Payment::where('type', 'incoming')->latest()->first();
        $this->assertStringContainsString('PAY-IN-', $payment->payment_number);
        $this->assertMatchesRegularExpression('/PAY-IN-\d{14}-\d+/', $payment->payment_number);
    }
}