<?php

namespace Tests\Unit\Properties;

use App\Services\AuditService;
use App\Services\BCMathCalculatorService;
use App\Services\DiscountValidatorService;
use App\Services\InvoiceCalculationService;
use App\Services\TaxCalculatorService;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Property 1: Grand Total Round-Trip
 *
 * grand_total == sum(line_subtotals) - sum(discounts) + sum(taxes) + surcharge + ematerai_fee
 *
 * Validates: Requirements 5.6, 5.8
 *
 * @group property-based
 */
class InvoiceCalculationProperty1Test extends TestCase
{
    private InvoiceCalculationService $service;
    private string $threshold = '5000000.00';

    protected function setUp(): void
    {
        parent::setUp();

        $auditService = Mockery::mock(AuditService::class);
        $auditService->shouldReceive('log')->byDefault();

        $calculator        = new BCMathCalculatorService();
        $discountValidator = new DiscountValidatorService($calculator, $auditService);
        $taxCalculator     = new TaxCalculatorService($calculator, $auditService);

        // Partial mock so getEMeteraiThreshold() returns a known value without DB
        $this->service = Mockery::mock(
            InvoiceCalculationService::class,
            [$calculator, $discountValidator, $taxCalculator, $auditService]
        )->makePartial();

        $this->service->shouldReceive('getEMeteraiThreshold')
            ->andReturn($this->threshold);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Property 1: grand_total == sum(line_subtotals) - sum(discounts) + sum(taxes) + surcharge + ematerai_fee
     */
    public function test_grand_total_round_trip_property(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            $lineCount = mt_rand(1, 10);
            $lineItems = [];

            for ($j = 0; $j < $lineCount; $j++) {
                $unitPrice  = (string) mt_rand(1000, 500000);
                $qty        = (string) mt_rand(1, 100);
                $discPct    = mt_rand(0, 20);
                $taxRate    = (mt_rand(0, 1) === 1) ? '11.00' : '0.00';

                $lineSubtotal   = bcmul($unitPrice, $qty, 2);
                $discountAmount = bcdiv(bcmul($lineSubtotal, (string) $discPct, 4), '100', 2);
                $dpp            = bcsub($lineSubtotal, $discountAmount, 2);

                // floor(dpp * rate / 100)
                $taxRaw    = bcdiv(bcmul($dpp, $taxRate, 10), '100', 10);
                $taxAmount = number_format((float) floor((float) $taxRaw), 2, '.', '');

                $lineItems[] = [
                    'line_subtotal'   => $lineSubtotal,
                    'discount_amount' => $discountAmount,
                    'tax_amount'      => $taxAmount,
                    'line_total'      => bcadd($dpp, $taxAmount, 2),
                ];
            }

            $surcharge = (string) mt_rand(0, 50000);

            $result = $this->service->calculateGrandTotal($lineItems, $surcharge);

            // Manually compute expected values
            $expectedSubtotal = '0.00';
            $expectedDiscount = '0.00';
            $expectedTax      = '0.00';

            foreach ($lineItems as $item) {
                $expectedSubtotal = bcadd($expectedSubtotal, $item['line_subtotal'], 2);
                $expectedDiscount = bcadd($expectedDiscount, $item['discount_amount'], 2);
                $expectedTax      = bcadd($expectedTax, $item['tax_amount'], 2);
            }

            $nett = bcadd(bcsub($expectedSubtotal, $expectedDiscount, 2), $expectedTax, 2);
            $nett = bcadd($nett, $surcharge, 2);

            $expectedEmaterai = (bccomp($nett, $this->threshold, 2) >= 0) ? '10000.00' : '0.00';
            $expectedGrand    = bcadd($nett, $expectedEmaterai, 2);

            $this->assertEquals(
                $expectedGrand,
                $result['grand_total'],
                "Iteration {$i}: grand_total mismatch. " .
                "Expected {$expectedGrand}, got {$result['grand_total']}. " .
                "lineCount={$lineCount}, surcharge={$surcharge}"
            );

            $this->assertEquals(
                $expectedTax,
                $result['tax_total'],
                "Iteration {$i}: tax_total mismatch."
            );

            $this->assertEquals(
                $expectedEmaterai,
                $result['ematerai_fee'],
                "Iteration {$i}: ematerai_fee mismatch."
            );
        }
    }
}
