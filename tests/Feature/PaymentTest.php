<?php

namespace Tests\Feature;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\SupplierInvoiceStatus;
use App\Models\BankAccount;
use App\Models\CustomerInvoice;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PurchaseOrder;
use App\Models\SupplierInvoice;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = app(PaymentService::class);
        Storage::fake('public');
    }

    // -----------------------------------------------------------------------
    // INCOMING PAYMENT TESTS (Customer → Medikindo)
    // -----------------------------------------------------------------------

    public function test_can_process_incoming_payment_full(): void
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

        $data = [
            'customer_invoice_id' => $invoice->id,
            'amount' => 500000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'sender_bank_name' => 'BCA',
            'sender_account_number' => '1234567890',
            'bank_account_id' => $bankAccount->id,
            'reference' => 'TRF123456',
            'notes' => 'Full payment',
            'payment_proof_file' => $file
        ];

        $payment = $this->paymentService->processIncomingPayment($data, $invoice);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals('incoming', $payment->type);
        $this->assertEquals(500000, $payment->amount);
        $this->assertEquals('Bank Transfer', $payment->payment_method);
        $this->assertEquals('completed', $payment->status);
        $this->assertStringContainsString('PAY-IN-', $payment->payment_number);

        // Check payment allocation
        $this->assertCount(1, $payment->allocations);
        $allocation = $payment->allocations->first();
        $this->assertEquals($invoice->id, $allocation->customer_invoice_id);
        $this->assertEquals(500000, $allocation->allocated_amount);

        // Check invoice status update
        $invoice->refresh();
        $this->assertEquals(500000, $invoice->paid_amount);
        $this->assertEquals(CustomerInvoiceStatus::PAID, $invoice->status);

        // Check bank account balance update
        $bankAccount->refresh();
        $this->assertEquals(1500000, $bankAccount->current_balance); // 1000000 + 500000
    }

    public function test_can_process_incoming_payment_partial(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()
            ->state(['current_balance' => 1000000])
            ->create();

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 1000000,
                'paid_amount' => 0,
                'status' => CustomerInvoiceStatus::ISSUED
            ])
            ->create();

        $data = [
            'customer_invoice_id' => $invoice->id,
            'amount' => 600000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'sender_bank_name' => 'Mandiri',
            'sender_account_number' => '9876543210',
            'bank_account_id' => $bankAccount->id,
            'reference' => 'TRF789012',
            'notes' => 'Partial payment'
        ];

        $payment = $this->paymentService->processIncomingPayment($data, $invoice);

        $this->assertEquals(600000, $payment->amount);

        // Check invoice status - should be partial paid
        $invoice->refresh();
        $this->assertEquals(600000, $invoice->paid_amount);
        $this->assertEquals(CustomerInvoiceStatus::PARTIAL_PAID, $invoice->status);

        // Check bank account balance
        $bankAccount->refresh();
        $this->assertEquals(1600000, $bankAccount->current_balance);
    }

    public function test_cannot_process_incoming_payment_exceeding_outstanding(): void
    {
        $organization = Organization::factory()->create();
        
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 500000,
                'paid_amount' => 200000, // Already paid 200k
                'status' => CustomerInvoiceStatus::PARTIAL_PAID
            ])
            ->create();

        $data = [
            'customer_invoice_id' => $invoice->id,
            'amount' => 400000, // Trying to pay 400k when only 300k outstanding
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('melebihi sisa tagihan');
        
        $this->paymentService->processIncomingPayment($data, $invoice);
    }

    public function test_cannot_process_incoming_payment_with_zero_amount(): void
    {
        $organization = Organization::factory()->create();
        
        $invoice = CustomerInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $data = [
            'customer_invoice_id' => $invoice->id,
            'amount' => 0,
            'payment_method' => 'Bank Transfer',
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('harus lebih dari 0');
        
        $this->paymentService->processIncomingPayment($data, $invoice);
    }

    // -----------------------------------------------------------------------
    // OUTGOING PAYMENT TESTS (Medikindo → Supplier)
    // -----------------------------------------------------------------------

    public function test_can_process_outgoing_payment_full(): void
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
                'paid_amount' => 1000000, // Fully paid by customer
                'status' => CustomerInvoiceStatus::PAID
            ])
            ->create();

        $data = [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 800000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Bank Transfer',
            'bank_account_id' => $bankAccount->id,
            'reference' => 'PAY-SUP-001',
            'description' => 'Payment to supplier'
        ];

        $payment = $this->paymentService->processOutgoingPayment($data, $supplierInvoice);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals('outgoing', $payment->type);
        $this->assertEquals(800000, $payment->amount);
        $this->assertEquals('completed', $payment->status);
        $this->assertStringContainsString('PAY-OUT-', $payment->payment_number);

        // Check payment allocation
        $this->assertCount(1, $payment->allocations);
        $allocation = $payment->allocations->first();
        $this->assertEquals($supplierInvoice->id, $allocation->supplier_invoice_id);
        $this->assertEquals(800000, $allocation->allocated_amount);

        // Check supplier invoice status update
        $supplierInvoice->refresh();
        $this->assertEquals(800000, $supplierInvoice->paid_amount);
        $this->assertEquals(SupplierInvoiceStatus::PAID, $supplierInvoice->status);

        // Check bank account balance update (debit)
        $bankAccount->refresh();
        $this->assertEquals(1200000, $bankAccount->current_balance); // 2000000 - 800000
    }

    public function test_cannot_process_outgoing_payment_for_unverified_invoice(): void
    {
        $organization = Organization::factory()->create();
        
        $supplierInvoice = SupplierInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => SupplierInvoiceStatus::DRAFT // Not verified
            ])
            ->create();

        $data = [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 500000,
            'payment_method' => 'Bank Transfer',
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('belum diverifikasi');
        
        $this->paymentService->processOutgoingPayment($data, $supplierInvoice);
    }

    public function test_cannot_process_outgoing_payment_without_customer_payment(): void
    {
        $organization = Organization::factory()->create();
        
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

        // Customer invoice exists but not paid enough
        $customerInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'goods_receipt_id' => $supplierInvoice->goods_receipt_id,
                'total_amount' => 1000000,
                'paid_amount' => 500000, // Only 500k paid, trying to pay supplier 800k
                'status' => CustomerInvoiceStatus::PARTIAL_PAID
            ])
            ->create();

        $data = [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 800000,
            'payment_method' => 'Bank Transfer',
        ];

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('RS/Klinik belum membayar cukup');
        
        $this->paymentService->processOutgoingPayment($data, $supplierInvoice);
    }

    public function test_can_process_outgoing_payment_partial(): void
    {
        $organization = Organization::factory()->create();
        
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
                'paid_amount' => 800000, // Customer paid 800k
            ])
            ->create();

        $data = [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 600000, // Pay supplier 600k (within customer payment)
            'payment_method' => 'Bank Transfer',
        ];

        $payment = $this->paymentService->processOutgoingPayment($data, $supplierInvoice);

        $this->assertEquals(600000, $payment->amount);

        // Check supplier invoice status - should remain VERIFIED (partial payment)
        $supplierInvoice->refresh();
        $this->assertEquals(600000, $supplierInvoice->paid_amount);
        $this->assertEquals(SupplierInvoiceStatus::VERIFIED, $supplierInvoice->status);
    }

    // -----------------------------------------------------------------------
    // PAYMENT WITH SURCHARGE TESTS
    // -----------------------------------------------------------------------

    public function test_can_process_outgoing_payment_with_surcharge(): void
    {
        $organization = Organization::factory()->create();
        
        $bankAccount = BankAccount::factory()
            ->state(['current_balance' => 2000000])
            ->create();

        $po = PurchaseOrder::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $supplierInvoice = SupplierInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'total_amount' => 500000,
                'status' => SupplierInvoiceStatus::VERIFIED
            ])
            ->create();

        $customerInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'purchase_order_id' => $po->id,
                'goods_receipt_id' => $supplierInvoice->goods_receipt_id,
                'paid_amount' => 600000, // Enough to cover payment + surcharge
            ])
            ->create();

        $data = [
            'supplier_invoice_id' => $supplierInvoice->id,
            'amount' => 500000,
            'payment_method' => 'Bank Transfer',
            'bank_account_id' => $bankAccount->id,
            'surcharge_amount' => 25000,
            'surcharge_percentage' => 5.0,
        ];

        $payment = $this->paymentService->processOutgoingPayment($data, $supplierInvoice);

        $this->assertEquals(500000, $payment->amount);
        $this->assertEquals(25000, $payment->surcharge_amount);
        $this->assertEquals(5.0, $payment->surcharge_percentage);
        $this->assertEquals(525000, $payment->total_with_surcharge); // 500k + 25k

        // Bank account should be debited by base amount only (surcharge is separate)
        $bankAccount->refresh();
        $this->assertEquals(1500000, $bankAccount->current_balance); // 2000000 - 500000
    }

    // -----------------------------------------------------------------------
    // PAYMENT GIRO/CEK TESTS
    // -----------------------------------------------------------------------

    public function test_can_process_incoming_payment_with_giro(): void
    {
        $organization = Organization::factory()->create();
        
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 750000,
                'paid_amount' => 0,
                'status' => CustomerInvoiceStatus::ISSUED
            ])
            ->create();

        $data = [
            'customer_invoice_id' => $invoice->id,
            'amount' => 750000,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Giro/Cek',
            'giro_number' => 'GR123456',
            'giro_due_date' => now()->addDays(30)->format('Y-m-d'),
            'issuing_bank' => 'Bank Mandiri',
            'giro_reference' => 'GIRO-REF-001',
        ];

        $payment = $this->paymentService->processIncomingPayment($data, $invoice);

        $this->assertEquals('Giro/Cek', $payment->payment_method);
        $this->assertEquals('GR123456', $payment->giro_number);
        $this->assertEquals('Bank Mandiri', $payment->issuing_bank);
        $this->assertNotNull($payment->giro_due_date);

        // Invoice should be marked as paid even with giro (trust-based)
        $invoice->refresh();
        $this->assertEquals(CustomerInvoiceStatus::PAID, $invoice->status);
    }

    // -----------------------------------------------------------------------
    // PAYMENT AUTHORIZATION TESTS
    // -----------------------------------------------------------------------

    public function test_healthcare_user_cannot_process_payments(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);
        
        $bankAccount = BankAccount::factory()
            ->create();
        
        $invoice = CustomerInvoice::factory()
            ->state(['organization_id' => $organization->id])
            ->create();

        $file = UploadedFile::fake()->image('payment_proof.jpg');

        $response = $this->postJson('/api/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 500000,
            'payment_method' => 'Bank Transfer',
            'bank_account_id' => $bankAccount->id,
            'sender_bank_name' => 'BCA',
            'sender_account_number' => '1234567890',
            'reference' => 'TRF123456',
            'payment_proof_file' => $file
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Forbidden.']);
    }

    public function test_finance_user_can_process_payments(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);
        
        $bankAccount = BankAccount::factory()
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

        $response = $this->postJson('/api/payments/incoming', [
            'customer_invoice_id' => $invoice->id,
            'amount' => 500000,
            'payment_method' => 'Bank Transfer',
            'sender_bank_name' => 'BCA',
            'sender_account_number' => '1234567890',
            'bank_account_id' => $bankAccount->id,
            'reference' => 'TRF123456',
            'payment_proof_file' => $file
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'payment_number',
                'type',
                'amount',
                'status'
            ]
        ]);
    }

    // -----------------------------------------------------------------------
    // PAYMENT LISTING AND FILTERING TESTS
    // -----------------------------------------------------------------------

    public function test_can_list_payments_with_filtering(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create different types of payments
        $incomingPayment = Payment::factory()
            ->state([
                'organization_id' => $organization->id,
                'type' => 'incoming'
            ])
            ->create();

        $outgoingPayment = Payment::factory()
            ->state([
                'organization_id' => $organization->id,
                'type' => 'outgoing'
            ])
            ->create();

        // Test listing all payments
        $response = $this->getJson('/api/payments');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'payment_number',
                    'type',
                    'amount',
                    'status',
                    'organization',
                    'allocations'
                ]
            ]
        ]);

        // Test filtering by type
        $response = $this->getJson('/api/payments?type=incoming');
        $response->assertStatus(200);
        
        $payments = $response->json('data');
        foreach ($payments as $payment) {
            $this->assertEquals('incoming', $payment['type']);
        }
    }

    // -----------------------------------------------------------------------
    // PAYMENT BUSINESS LOGIC TESTS
    // -----------------------------------------------------------------------

    public function test_payment_service_helper_methods(): void
    {
        $organization = Organization::factory()->create();
        
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'total_amount' => 1000000,
                'paid_amount' => 600000
            ])
            ->create();

        // Create some payments for this invoice
        $payment1 = Payment::factory()
            ->state(['amount' => 400000])
            ->create();
        
        $payment2 = Payment::factory()
            ->state(['amount' => 200000])
            ->create();

        PaymentAllocation::factory()
            ->state([
                'payment_id' => $payment1->id,
                'customer_invoice_id' => $invoice->id,
                'allocated_amount' => 400000
            ])
            ->create();

        PaymentAllocation::factory()
            ->state([
                'payment_id' => $payment2->id,
                'customer_invoice_id' => $invoice->id,
                'allocated_amount' => 200000
            ])
            ->create();

        $totalPayments = $this->paymentService->getTotalPayments($invoice);
        $this->assertEquals(600000, $totalPayments);

        $isConsistent = $this->paymentService->validatePaymentConsistency($invoice);
        $this->assertTrue($isConsistent);
    }

    // -----------------------------------------------------------------------
    // PAYMENT MODEL TESTS
    // -----------------------------------------------------------------------

    public function test_payment_model_relationships(): void
    {
        $organization = Organization::factory()->create();
        $bankAccount = BankAccount::factory()->create();

        $payment = Payment::factory()
            ->state([
                'organization_id' => $organization->id,
                'bank_account_id' => $bankAccount->id
            ])
            ->create();

        $this->assertEquals($organization->id, $payment->organization->id);
        $this->assertEquals($bankAccount->id, $payment->bankAccount->id);
        // Note: Payment model doesn't have user relationship in current schema
    }

    public function test_payment_model_attributes(): void
    {
        $payment = Payment::factory()
            ->state([
                'amount' => 500000,
                'surcharge_amount' => 25000,
                'payment_method' => 'Bank Transfer'
            ])
            ->create();

        $this->assertEquals(525000, $payment->total_with_surcharge);
        $this->assertEquals('Transfer Bank', $payment->payment_method_label);
    }

    // -----------------------------------------------------------------------
    // PAYMENT ALLOCATION TESTS
    // -----------------------------------------------------------------------

    public function test_payment_allocation_relationships(): void
    {
        $payment = Payment::factory()->create();
        $customerInvoice = CustomerInvoice::factory()->create();
        $supplierInvoice = SupplierInvoice::factory()->create();

        $allocation1 = PaymentAllocation::factory()
            ->state([
                'payment_id' => $payment->id,
                'customer_invoice_id' => $customerInvoice->id,
                'allocated_amount' => 300000
            ])
            ->create();

        $allocation2 = PaymentAllocation::factory()
            ->state([
                'payment_id' => $payment->id,
                'supplier_invoice_id' => $supplierInvoice->id,
                'allocated_amount' => 200000
            ])
            ->create();

        $this->assertEquals($payment->id, $allocation1->payment->id);
        $this->assertEquals($customerInvoice->id, $allocation1->customerInvoice->id);
        $this->assertEquals($supplierInvoice->id, $allocation2->supplierInvoice->id);
    }

    // -----------------------------------------------------------------------
    // MULTI-TENANT SECURITY TESTS
    // -----------------------------------------------------------------------

    public function test_payment_multi_tenant_isolation(): void
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

        // User from org1 should only see org1 payments
        $response = $this->getJson('/api/payments');
        $response->assertStatus(200);
        
        $paymentIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($payment1->id, $paymentIds);
        $this->assertNotContains($payment2->id, $paymentIds);
    }

    public function test_cannot_access_other_organization_payment(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $user = $this->actingAsFinanceUser($org1);
        
        $payment = Payment::factory()
            ->state(['organization_id' => $org2->id])
            ->create();

        $response = $this->getJson("/api/payments/{$payment->id}");
        $response->assertStatus(403);
    }
}