<?php

namespace Tests\Unit\Services;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditServiceInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private AuditService $auditService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $organization->id,
        ]);
        
        $this->auditService = new AuditService();
    }

    public function test_it_logs_calculation()
    {
        $log = $this->auditService->logCalculation(
            operation: 'line_item_calculation',
            inputs: ['quantity' => '10.000', 'unit_price' => '100.00'],
            output: ['line_total' => '1000.00'],
            invoiceId: 1,
            userId: $this->user->id
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('invoice.calculation', $log->action);
        $this->assertEquals('invoice_calculation', $log->entity_type);
        $this->assertEquals(1, $log->entity_id);
        $this->assertEquals($this->user->id, $log->user_id);
        $this->assertArrayHasKey('operation', $log->metadata);
        $this->assertEquals('line_item_calculation', $log->metadata['operation']);
    }

    public function test_it_logs_validation_failure()
    {
        $log = $this->auditService->logValidationFailure(
            rule: 'discount_percentage_range',
            inputs: ['discount_percentage' => '150.00'],
            reason: 'Discount percentage must be between 0 and 100',
            invoiceId: 1,
            userId: $this->user->id
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('invoice.validation_failure', $log->action);
        $this->assertEquals('invoice_validation', $log->entity_type);
        $this->assertArrayHasKey('rule', $log->metadata);
        $this->assertArrayHasKey('reason', $log->metadata);
    }

    public function test_it_logs_discrepancy()
    {
        $log = $this->auditService->logDiscrepancy(
            invoiceId: 1,
            expectedTotal: '1000.00',
            actualTotal: '1015.00',
            varianceAmount: '15.00',
            variancePercentage: '1.50',
            flagged: true,
            userId: $this->user->id
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('invoice.discrepancy_detected', $log->action);
        $this->assertEquals('invoice_discrepancy', $log->entity_type);
        $this->assertEquals('1000.00', $log->metadata['expected_total']);
        $this->assertEquals('1015.00', $log->metadata['actual_total']);
        $this->assertEquals('15.00', $log->metadata['variance_amount']);
        $this->assertEquals('1.50', $log->metadata['variance_percentage']);
        $this->assertTrue($log->metadata['flagged']);
    }

    public function test_it_logs_immutability_violation()
    {
        $log = $this->auditService->logImmutabilityViolation(
            invoiceId: 1,
            invoiceType: 'supplier',
            attemptedChanges: ['total_amount' => '2000.00'],
            violations: ['total_amount' => ['reason' => 'Field is immutable']],
            userId: $this->user->id
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('invoice.immutability_violation', $log->action);
        $this->assertEquals('supplier_invoice', $log->entity_type);
        $this->assertArrayHasKey('attempted_changes', $log->metadata);
        $this->assertArrayHasKey('violations', $log->metadata);
        $this->assertArrayHasKey('violated_fields', $log->metadata);
        $this->assertContains('total_amount', $log->metadata['violated_fields']);
    }

    public function test_it_logs_concurrency_conflict()
    {
        $log = $this->auditService->logConcurrencyConflict(
            invoiceId: 1,
            invoiceType: 'customer',
            expectedVersion: 0,
            attemptedChanges: ['status' => 'paid'],
            userId: $this->user->id
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('invoice.concurrency_conflict', $log->action);
        $this->assertEquals('customer_invoice', $log->entity_type);
        $this->assertEquals(0, $log->metadata['expected_version']);
        $this->assertEquals('optimistic_locking_failure', $log->metadata['conflict_type']);
    }

    public function test_it_logs_line_items_created()
    {
        $lineItems = [
            ['product_id' => 1, 'quantity' => '10.000', 'line_total' => '1000.00'],
            ['product_id' => 2, 'quantity' => '5.000', 'line_total' => '500.00'],
        ];

        $log = $this->auditService->logLineItemsCreated(
            invoiceId: 1,
            invoiceType: 'supplier',
            lineItems: $lineItems,
            userId: $this->user->id
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('invoice.line_items_created', $log->action);
        $this->assertEquals(2, $log->metadata['line_items_count']);
        $this->assertCount(2, $log->metadata['line_items']);
    }

    public function test_it_logs_tolerance_check()
    {
        $log = $this->auditService->logToleranceCheck(
            invoiceId: 1,
            calculatedTotal: '1000.00',
            invoiceTotal: '1000.01',
            difference: '0.01',
            passed: true,
            userId: $this->user->id
        );

        $this->assertInstanceOf(AuditLog::class, $log);
        $this->assertEquals('invoice.tolerance_check', $log->action);
        $this->assertEquals('0.01', $log->metadata['tolerance']);
        $this->assertTrue($log->metadata['passed']);
    }

    public function test_it_queries_invoice_audit_trail()
    {
        // Create multiple audit logs
        $this->auditService->logCalculation('test', [], [], 1, $this->user->id);
        $this->auditService->logDiscrepancy(1, '1000', '1015', '15', '1.5', true, $this->user->id);
        $this->auditService->logCalculation('test', [], [], 2, $this->user->id);

        $trail = $this->auditService->getInvoiceAuditTrail(1);

        $this->assertCount(2, $trail);
        $this->assertEquals(1, $trail->first()->entity_id);
    }

    public function test_it_queries_invoice_audit_trail_with_action_filter()
    {
        $this->auditService->logCalculation('test', [], [], 1, $this->user->id);
        $this->auditService->logDiscrepancy(1, '1000', '1015', '15', '1.5', true, $this->user->id);

        $trail = $this->auditService->getInvoiceAuditTrail(1, 'invoice.calculation');

        $this->assertCount(1, $trail);
        $this->assertEquals('invoice.calculation', $trail->first()->action);
    }

    public function test_it_queries_calculation_audit_trail()
    {
        $this->auditService->logCalculation('line_item', [], [], 1, $this->user->id);
        $this->auditService->logCalculation('invoice_totals', [], [], 1, $this->user->id);
        $this->auditService->logCalculation('line_item', [], [], 2, $this->user->id);

        $trail = $this->auditService->getCalculationAuditTrail(1);

        $this->assertCount(2, $trail);
    }

    public function test_it_queries_calculation_audit_trail_with_operation_filter()
    {
        $this->auditService->logCalculation('line_item', [], [], 1, $this->user->id);
        $this->auditService->logCalculation('invoice_totals', [], [], 1, $this->user->id);

        $trail = $this->auditService->getCalculationAuditTrail(1, 'line_item');

        $this->assertCount(1, $trail);
        $this->assertEquals('line_item', $trail->first()->metadata['operation']);
    }

    public function test_it_queries_discrepancy_audit_trail()
    {
        $this->auditService->logDiscrepancy(1, '1000', '1015', '15', '1.5', true, $this->user->id);
        $this->auditService->logDiscrepancy(2, '2000', '2005', '5', '0.25', false, $this->user->id);

        $trail = $this->auditService->getDiscrepancyAuditTrail();

        $this->assertCount(2, $trail);
    }

    public function test_it_queries_flagged_discrepancies_only()
    {
        $this->auditService->logDiscrepancy(1, '1000', '1015', '15', '1.5', true, $this->user->id);
        $this->auditService->logDiscrepancy(2, '2000', '2005', '5', '0.25', false, $this->user->id);

        $trail = $this->auditService->getDiscrepancyAuditTrail(flaggedOnly: true);

        $this->assertCount(1, $trail);
        $this->assertTrue($trail->first()->metadata['flagged']);
    }

    public function test_it_queries_immutability_violations()
    {
        $this->auditService->logImmutabilityViolation(1, 'supplier', [], [], $this->user->id);
        $this->auditService->logImmutabilityViolation(2, 'customer', [], [], $this->user->id);

        $violations = $this->auditService->getImmutabilityViolations();

        $this->assertCount(2, $violations);
    }

    public function test_it_queries_immutability_violations_by_user()
    {
        $user2 = User::factory()->create(['organization_id' => $this->user->organization_id]);

        $this->auditService->logImmutabilityViolation(1, 'supplier', [], [], $this->user->id);
        $this->auditService->logImmutabilityViolation(2, 'customer', [], [], $user2->id);

        $violations = $this->auditService->getImmutabilityViolations($this->user->id);

        $this->assertCount(1, $violations);
        $this->assertEquals($this->user->id, $violations->first()->user_id);
    }

    public function test_it_queries_concurrency_conflicts()
    {
        $this->auditService->logConcurrencyConflict(1, 'supplier', 0, [], $this->user->id);
        $this->auditService->logConcurrencyConflict(2, 'customer', 1, [], $this->user->id);

        $conflicts = $this->auditService->getConcurrencyConflicts();

        $this->assertCount(2, $conflicts);
    }

    public function test_it_queries_concurrency_conflicts_by_invoice()
    {
        $this->auditService->logConcurrencyConflict(1, 'supplier', 0, [], $this->user->id);
        $this->auditService->logConcurrencyConflict(2, 'customer', 1, [], $this->user->id);

        $conflicts = $this->auditService->getConcurrencyConflicts(1);

        $this->assertCount(1, $conflicts);
        $this->assertEquals(1, $conflicts->first()->entity_id);
    }

    public function test_all_logs_include_timestamps()
    {
        $log = $this->auditService->logCalculation('test', [], [], 1, $this->user->id);

        $this->assertArrayHasKey('timestamp', $log->metadata);
        $this->assertNotEmpty($log->metadata['timestamp']);
    }

    public function test_all_logs_preserve_bcmath_precision()
    {
        $log = $this->auditService->logDiscrepancy(
            invoiceId: 1,
            expectedTotal: '1000.00',
            actualTotal: '1015.50',
            varianceAmount: '15.50',
            variancePercentage: '1.55',
            flagged: true,
            userId: $this->user->id
        );

        // Values should be stored as strings to preserve precision
        $this->assertIsString($log->metadata['expected_total']);
        $this->assertIsString($log->metadata['actual_total']);
        $this->assertIsString($log->metadata['variance_amount']);
        $this->assertIsString($log->metadata['variance_percentage']);
        $this->assertEquals('1000.00', $log->metadata['expected_total']);
        $this->assertEquals('1015.50', $log->metadata['actual_total']);
    }
}
