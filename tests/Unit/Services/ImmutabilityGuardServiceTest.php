<?php

namespace Tests\Unit\Services;

use App\Exceptions\ImmutabilityViolationException;
use App\Models\CustomerInvoice;
use App\Models\InvoiceModificationAttempt;
use App\Models\SupplierInvoice;
use App\Services\AuditService;
use App\Services\ImmutabilityGuardService;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class ImmutabilityGuardServiceTest extends TestCase
{
    private ImmutabilityGuardService $immutabilityGuard;
    private AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->auditService = Mockery::mock(AuditService::class);
        $this->auditService->shouldReceive('log')->byDefault();
        
        $this->immutabilityGuard = new ImmutabilityGuardService($this->auditService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMockInvoice(string $status = 'issued', string $type = 'supplier'): Model
    {
        $class = $type === 'supplier' ? SupplierInvoice::class : CustomerInvoice::class;
        $invoice = Mockery::mock($class)->makePartial();
        $invoice->id = 1;
        $invoice->invoice_number = 'INV-TEST-001';
        $invoice->status = $status;
        $invoice->total_amount = '1000.00';
        
        return $invoice;
    }

    public function test_it_allows_modifications_to_draft_invoices()
    {
        $invoice = $this->createMockInvoice('draft');
        
        $attemptedChanges = [
            'total_amount' => '2000.00',
            'discount_amount' => '100.00',
        ];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['violations']);
        $this->assertStringContainsString('draft', $result['message']);
    }

    public function test_it_blocks_modifications_to_issued_invoices()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = [
            'total_amount' => '2000.00',
        ];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertNotEmpty($result['violations']);
        $this->assertArrayHasKey('total_amount', $result['violations']);
    }

    public function test_it_allows_mutable_field_changes()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = [
            'status' => 'paid',
            'paid_amount' => '1000.00',
            'payment_reference' => 'PAY-001',
            'notes' => 'Payment received',
        ];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['violations']);
    }

    public function test_it_blocks_total_amount_modification()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = ['total_amount' => '2000.00'];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('total_amount', $result['violations']);
        $this->assertEquals('Field is immutable after invoice issuance', 
            $result['violations']['total_amount']['reason']);
    }

    public function test_it_blocks_subtotal_amount_modification()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = ['subtotal_amount' => '1500.00'];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('subtotal_amount', $result['violations']);
    }

    public function test_it_blocks_discount_amount_modification()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = ['discount_amount' => '200.00'];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('discount_amount', $result['violations']);
    }

    public function test_it_blocks_tax_amount_modification()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = ['tax_amount' => '150.00'];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('tax_amount', $result['violations']);
    }

    public function test_it_blocks_invoice_number_modification()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = ['invoice_number' => 'INV-NEW-001'];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertArrayHasKey('invoice_number', $result['violations']);
    }

    public function test_it_blocks_multiple_immutable_fields()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = [
            'total_amount' => '2000.00',
            'discount_amount' => '100.00',
            'tax_amount' => '200.00',
        ];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertCount(3, $result['violations']);
        $this->assertArrayHasKey('total_amount', $result['violations']);
        $this->assertArrayHasKey('discount_amount', $result['violations']);
        $this->assertArrayHasKey('tax_amount', $result['violations']);
    }

    public function test_it_allows_mixed_mutable_and_immutable_when_only_mutable_changed()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = [
            'status' => 'paid',
            'notes' => 'Updated notes',
        ];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertTrue($result['is_valid']);
    }

    public function test_it_detects_violations_in_mixed_changes()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = [
            'status' => 'paid',           // Mutable - OK
            'total_amount' => '2000.00',  // Immutable - VIOLATION
            'notes' => 'Updated',         // Mutable - OK
        ];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertCount(1, $result['violations']);
        $this->assertArrayHasKey('total_amount', $result['violations']);
    }

    public function test_it_checks_immutability_for_all_immutable_statuses()
    {
        $immutableStatuses = ['issued', 'pending_approval', 'approved', 'paid', 'partially_paid', 'verified', 'overdue'];
        
        foreach ($immutableStatuses as $status) {
            $invoice = $this->createMockInvoice($status);
            
            $this->assertTrue(
                $this->immutabilityGuard->isImmutable($invoice),
                "Status '{$status}' should be immutable"
            );
        }
    }

    public function test_it_allows_modifications_for_draft_status()
    {
        $invoice = $this->createMockInvoice('draft');
        
        $this->assertFalse($this->immutabilityGuard->isImmutable($invoice));
    }

    public function test_it_identifies_immutable_fields()
    {
        $this->assertTrue($this->immutabilityGuard->isFieldImmutable('total_amount'));
        $this->assertTrue($this->immutabilityGuard->isFieldImmutable('subtotal_amount'));
        $this->assertTrue($this->immutabilityGuard->isFieldImmutable('discount_amount'));
        $this->assertTrue($this->immutabilityGuard->isFieldImmutable('tax_amount'));
        $this->assertTrue($this->immutabilityGuard->isFieldImmutable('invoice_number'));
    }

    public function test_it_identifies_mutable_fields()
    {
        $this->assertTrue($this->immutabilityGuard->isFieldMutable('status'));
        $this->assertTrue($this->immutabilityGuard->isFieldMutable('paid_amount'));
        $this->assertTrue($this->immutabilityGuard->isFieldMutable('payment_reference'));
        $this->assertTrue($this->immutabilityGuard->isFieldMutable('notes'));
    }

    public function test_it_returns_immutable_fields_list()
    {
        $fields = $this->immutabilityGuard->getImmutableFields();
        
        $this->assertIsArray($fields);
        $this->assertContains('total_amount', $fields);
        $this->assertContains('subtotal_amount', $fields);
        $this->assertContains('discount_amount', $fields);
        $this->assertContains('tax_amount', $fields);
    }

    public function test_it_returns_mutable_fields_list()
    {
        $fields = $this->immutabilityGuard->getMutableFields();
        
        $this->assertIsArray($fields);
        $this->assertContains('status', $fields);
        $this->assertContains('paid_amount', $fields);
        $this->assertContains('payment_reference', $fields);
    }

    public function test_it_throws_exception_on_enforce_with_violations()
    {
        $this->expectException(ImmutabilityViolationException::class);
        $this->expectExceptionMessage('total_amount');
        
        $invoice = $this->createMockInvoice('issued');
        
        // Mock InvoiceModificationAttempt::create to avoid database
        $attemptedChanges = ['total_amount' => '2000.00'];
        
        $this->immutabilityGuard->enforce($invoice, $attemptedChanges);
    }

    public function test_it_does_not_throw_exception_on_enforce_with_valid_changes()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = ['status' => 'paid'];
        
        // Should not throw exception
        $this->immutabilityGuard->enforce($invoice, $attemptedChanges);
        
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_it_validates_invoice_type()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SupplierInvoice or CustomerInvoice');
        
        $invalidModel = Mockery::mock(Model::class)->makePartial();
        $invalidModel->shouldReceive('getAttribute')->andReturn('issued');
        $invalidModel->status = 'issued';
        
        $this->immutabilityGuard->checkImmutability($invalidModel, []);
    }

    public function test_it_works_with_supplier_invoice()
    {
        $invoice = $this->createMockInvoice('issued', 'supplier');
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, ['status' => 'paid']);
        
        $this->assertTrue($result['is_valid']);
    }

    public function test_it_works_with_customer_invoice()
    {
        $invoice = $this->createMockInvoice('issued', 'customer');
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, ['status' => 'paid']);
        
        $this->assertTrue($result['is_valid']);
    }

    public function test_it_checks_line_item_immutability()
    {
        $attemptedChanges = [
            'quantity' => '20.000',
            'unit_price' => '150.00',
        ];
        
        $result = $this->immutabilityGuard->checkLineItemImmutability($attemptedChanges);
        
        $this->assertFalse($result['is_valid']);
        $this->assertCount(2, $result['violations']);
        $this->assertArrayHasKey('quantity', $result['violations']);
        $this->assertArrayHasKey('unit_price', $result['violations']);
    }

    public function test_it_allows_non_immutable_line_item_fields()
    {
        $attemptedChanges = [
            'notes' => 'Updated notes',
        ];
        
        $result = $this->immutabilityGuard->checkLineItemImmutability($attemptedChanges);
        
        $this->assertTrue($result['is_valid']);
        $this->assertEmpty($result['violations']);
    }

    public function test_it_formats_single_violation_message()
    {
        $violations = [
            'total_amount' => ['field' => 'total_amount', 'reason' => 'Immutable'],
        ];
        
        $message = $this->immutabilityGuard->formatViolationMessage($violations);
        
        $this->assertStringContainsString('total_amount', $message);
        $this->assertStringContainsString('tidak dapat diubah', $message);
    }

    public function test_it_formats_multiple_violations_message()
    {
        $violations = [
            'total_amount' => ['field' => 'total_amount', 'reason' => 'Immutable'],
            'discount_amount' => ['field' => 'discount_amount', 'reason' => 'Immutable'],
            'tax_amount' => ['field' => 'tax_amount', 'reason' => 'Immutable'],
        ];
        
        $message = $this->immutabilityGuard->formatViolationMessage($violations);
        
        $this->assertStringContainsString('total_amount', $message);
        $this->assertStringContainsString('discount_amount', $message);
        $this->assertStringContainsString('tax_amount', $message);
        $this->assertStringContainsString('dan', $message);
    }

    public function test_it_formats_empty_violations_message()
    {
        $message = $this->immutabilityGuard->formatViolationMessage([]);
        
        $this->assertStringContainsString('Tidak ada pelanggaran', $message);
    }

    public function test_it_includes_all_required_data_in_result()
    {
        $invoice = $this->createMockInvoice('issued');
        
        $attemptedChanges = ['total_amount' => '2000.00'];
        
        $result = $this->immutabilityGuard->checkImmutability($invoice, $attemptedChanges);
        
        $this->assertArrayHasKey('is_valid', $result);
        $this->assertArrayHasKey('violations', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('invoice_status', $result);
        $this->assertArrayHasKey('attempted_changes', $result);
    }
}
