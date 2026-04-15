<?php

namespace Tests\Unit\Properties;

use App\Exceptions\InvalidStateTransitionException;
use App\Models\CustomerInvoice;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Property 8: State Machine Valid Transitions Only
 *
 * Only transitions defined in CustomerInvoice::TRANSITIONS succeed;
 * all others throw InvalidStateTransitionException.
 *
 * Validates: Requirements 7.1, 7.2
 *
 * @group property-based
 */
class StateMachineProperty8Test extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeInvoice(string $status): CustomerInvoice
    {
        $invoice         = new CustomerInvoice();
        $invoice->status = $status;

        return $invoice;
    }

    /**
     * Property 8a: All valid transitions return true from canTransitionTo()
     */
    public function test_valid_transitions_return_true(): void
    {
        foreach (CustomerInvoice::TRANSITIONS as $from => $targets) {
            foreach ($targets as $to) {
                $invoice = $this->makeInvoice($from);

                $this->assertTrue(
                    $invoice->canTransitionTo($to),
                    "canTransitionTo() should return true for valid transition {$from} → {$to}"
                );
            }
        }
    }

    /**
     * Property 8b: All invalid transitions return false from canTransitionTo()
     */
    public function test_invalid_transitions_return_false(): void
    {
        $allStatuses = [
            CustomerInvoice::STATUS_DRAFT,
            CustomerInvoice::STATUS_ISSUED,
            CustomerInvoice::STATUS_PARTIAL_PAID,
            CustomerInvoice::STATUS_PAID,
            CustomerInvoice::STATUS_VOID,
        ];

        foreach ($allStatuses as $from) {
            $validTargets = CustomerInvoice::TRANSITIONS[$from] ?? [];

            foreach ($allStatuses as $to) {
                if (in_array($to, $validTargets, true)) {
                    continue; // skip valid transitions
                }

                $invoice = $this->makeInvoice($from);

                $this->assertFalse(
                    $invoice->canTransitionTo($to),
                    "canTransitionTo() should return false for invalid transition {$from} → {$to}"
                );
            }
        }
    }

    /**
     * Property 8c: transitionTo() throws InvalidStateTransitionException for invalid transitions
     */
    public function test_invalid_transitions_throw_exception(): void
    {
        $invalidTransitions = [
            [CustomerInvoice::STATUS_PAID,         CustomerInvoice::STATUS_DRAFT],
            [CustomerInvoice::STATUS_PAID,         CustomerInvoice::STATUS_ISSUED],
            [CustomerInvoice::STATUS_VOID,         CustomerInvoice::STATUS_ISSUED],
            [CustomerInvoice::STATUS_VOID,         CustomerInvoice::STATUS_DRAFT],
            [CustomerInvoice::STATUS_DRAFT,        CustomerInvoice::STATUS_PAID],
            [CustomerInvoice::STATUS_DRAFT,        CustomerInvoice::STATUS_VOID],
            [CustomerInvoice::STATUS_ISSUED,       CustomerInvoice::STATUS_DRAFT],
            [CustomerInvoice::STATUS_PARTIAL_PAID, CustomerInvoice::STATUS_DRAFT],
            [CustomerInvoice::STATUS_PARTIAL_PAID, CustomerInvoice::STATUS_ISSUED],
        ];

        foreach ($invalidTransitions as [$from, $to]) {
            // Use a partial mock so save() doesn't hit the DB
            $invoice = Mockery::mock(CustomerInvoice::class)->makePartial();
            $invoice->status = $from;
            $invoice->shouldReceive('save')->never();

            $this->expectException(InvalidStateTransitionException::class);

            $invoice->transitionTo($to);
        }
    }

    /**
     * Property 8d: transitionTo() succeeds (no exception) for all valid transitions
     * Uses mock to avoid DB save.
     */
    public function test_valid_transitions_do_not_throw(): void
    {
        foreach (CustomerInvoice::TRANSITIONS as $from => $targets) {
            foreach ($targets as $to) {
                $invoice = Mockery::mock(CustomerInvoice::class)->makePartial();
                $invoice->status = $from;
                $invoice->shouldReceive('save')->once()->andReturn(true);

                $exceptionThrown = false;

                try {
                    $invoice->transitionTo($to);
                } catch (InvalidStateTransitionException $e) {
                    $exceptionThrown = true;
                }

                $this->assertFalse(
                    $exceptionThrown,
                    "transitionTo() should NOT throw for valid transition {$from} → {$to}"
                );
            }
        }
    }
}
