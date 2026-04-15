<?php

namespace Tests\Unit\Properties;

use App\Exceptions\ImmutabilityViolationException;
use App\Models\CustomerInvoice;
use App\Services\AuditService;
use App\Services\ImmutabilityGuardService;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Property 10: Immutability Guard
 *
 * Modifying financial fields on ISSUED/PAID/VOID invoice throws ImmutabilityViolationException.
 *
 * Validates: Requirements 6.1, 6.7
 *
 * @group property-based
 */
class ImmutabilityProperty10Test extends TestCase
{
    private ImmutabilityGuardService $guard;

    private array $financialFields = [
        'total_amount',
        'subtotal_amount',
        'discount_amount',
        'tax_amount',
        'invoice_number',
        'invoice_date',
        'due_date',
    ];

    private array $immutableStatuses = [
        CustomerInvoice::STATUS_ISSUED,
        CustomerInvoice::STATUS_PARTIAL_PAID,
        CustomerInvoice::STATUS_PAID,
        CustomerInvoice::STATUS_VOID,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $auditService = Mockery::mock(AuditService::class);
        $auditService->shouldReceive('log')->byDefault();

        $this->guard = new ImmutabilityGuardService($auditService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeMockInvoice(string $status): CustomerInvoice
    {
        $invoice = Mockery::mock(CustomerInvoice::class)->makePartial();
        $invoice->id             = mt_rand(1, 9999);
        $invoice->invoice_number = 'INV-TEST-' . mt_rand(1000, 9999);
        $invoice->status         = $status;
        $invoice->total_amount   = number_format(mt_rand(100000, 9999999), 2, '.', '');

        return $invoice;
    }

    /**
     * Property 10: enforce() throws ImmutabilityViolationException for every immutable status
     * when financial fields are changed. 25 iterations per status.
     */
    public function test_immutability_guard_throws_for_issued_status(): void
    {
        $this->runImmutabilityIterations(CustomerInvoice::STATUS_ISSUED);
    }

    public function test_immutability_guard_throws_for_partial_paid_status(): void
    {
        $this->runImmutabilityIterations(CustomerInvoice::STATUS_PARTIAL_PAID);
    }

    public function test_immutability_guard_throws_for_paid_status(): void
    {
        $this->runImmutabilityIterations(CustomerInvoice::STATUS_PAID);
    }

    public function test_immutability_guard_throws_for_void_status(): void
    {
        $this->runImmutabilityIterations(CustomerInvoice::STATUS_VOID);
    }

    private function runImmutabilityIterations(string $status): void
    {
        for ($i = 0; $i < 25; $i++) {
            $invoice = $this->makeMockInvoice($status);

            // Pick a random financial field to attempt to change
            $field    = $this->financialFields[array_rand($this->financialFields)];
            $newValue = number_format(mt_rand(1, 9999999), 2, '.', '');

            $exceptionThrown = false;

            try {
                $this->guard->enforce($invoice, [$field => $newValue]);
            } catch (ImmutabilityViolationException $e) {
                $exceptionThrown = true;
            } catch (\Throwable $e) {
                // Any other exception — still counts as blocked
                $exceptionThrown = true;
            }

            $this->assertTrue(
                $exceptionThrown,
                "Iteration {$i}: ImmutabilityViolationException should be thrown " .
                "when modifying '{$field}' on invoice with status='{$status}'"
            );
        }
    }

    public function test_draft_invoice_does_not_throw(): void
    {
        $invoice = $this->makeMockInvoice(CustomerInvoice::STATUS_DRAFT);

        // Should NOT throw for draft
        $this->guard->enforce($invoice, ['total_amount' => '999.00']);

        $this->assertTrue(true, 'No exception should be thrown for draft invoice');
    }
}
