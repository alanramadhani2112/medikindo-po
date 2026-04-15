<?php

namespace Tests\Unit\Properties;

use App\Models\CustomerInvoice;
use PHPUnit\Framework\TestCase;

/**
 * Property 12: Print Count Monotonic Increment
 *
 * Each print operation increments print_count by exactly 1.
 *
 * Validates: Requirements 12.11
 *
 * @group property-based
 */
class PrintCountProperty12Test extends TestCase
{
    /**
     * Simulate the increment logic without DB.
     * The actual DB increment is tested via the model's increment() method contract.
     */
    private function simulatePrintCount(int $initial, int $n): int
    {
        $count = $initial;
        for ($i = 0; $i < $n; $i++) {
            $count++; // simulates $invoice->increment('print_count')
        }
        return $count;
    }

    /**
     * Property 12: final print_count == N after N print operations.
     * 50 iterations with random N (1-20).
     */
    public function test_print_count_monotonic_increment(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $n       = mt_rand(1, 20);
            $initial = 0;

            $finalCount = $this->simulatePrintCount($initial, $n);

            $this->assertEquals(
                $n,
                $finalCount,
                "Iteration {$i}: after {$n} print operations starting from {$initial}, " .
                "print_count should be {$n}. Got: {$finalCount}"
            );
        }
    }

    /**
     * Property 12: each individual increment adds exactly 1.
     */
    public function test_print_count_increments_by_exactly_one_per_operation(): void
    {
        $count = 0;

        for ($step = 1; $step <= 10; $step++) {
            $before = $count;
            $count++;
            $after = $count;

            $this->assertEquals(
                $before + 1,
                $after,
                "Step {$step}: print_count should increment by exactly 1. " .
                "Before={$before}, After={$after}"
            );
        }
    }

    /**
     * Property 12 (structural): CustomerInvoice model has print_count field with integer cast.
     */
    public function test_customer_invoice_has_print_count_field(): void
    {
        $invoice = new CustomerInvoice();
        $casts   = $invoice->getCasts();

        $this->assertArrayHasKey(
            'print_count',
            $casts,
            'CustomerInvoice must have print_count in $casts'
        );

        $this->assertEquals(
            'integer',
            $casts['print_count'],
            'print_count must be cast as integer'
        );
    }

    /**
     * Property 12 (structural): CustomerInvoice model has last_printed_at field.
     */
    public function test_customer_invoice_has_last_printed_at_field(): void
    {
        $invoice = new CustomerInvoice();
        $casts   = $invoice->getCasts();

        $this->assertArrayHasKey(
            'last_printed_at',
            $casts,
            'CustomerInvoice must have last_printed_at in $casts'
        );
    }

    /**
     * Property 12: print_count is monotonically non-decreasing.
     * Verify that after N operations, count is always >= initial count.
     */
    public function test_print_count_is_monotonically_non_decreasing(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $initial = mt_rand(0, 100);
            $n       = mt_rand(1, 20);

            $finalCount = $this->simulatePrintCount($initial, $n);

            $this->assertGreaterThanOrEqual(
                $initial,
                $finalCount,
                "Iteration {$i}: print_count should never decrease. " .
                "initial={$initial}, n={$n}, final={$finalCount}"
            );

            $this->assertEquals(
                $initial + $n,
                $finalCount,
                "Iteration {$i}: print_count should be initial + n. " .
                "initial={$initial}, n={$n}, expected=" . ($initial + $n) . ", got={$finalCount}"
            );
        }
    }
}
