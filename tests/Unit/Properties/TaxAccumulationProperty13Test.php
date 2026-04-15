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
 * Property 13: Tax Accumulation Consistency
 *
 * sum(line_items.tax_amount) == result['tax_total']
 *
 * Validates: Requirements 5.3
 *
 * @group property-based
 */
class TaxAccumulationProperty13Test extends TestCase
{
    private InvoiceCalculationService $service;

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
            ->andReturn('5000000.00');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Property 13: result['tax_total'] == sum of all line_items.tax_amount
     */
    public function test_tax_accumulation_consistency_property(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $lineCount = mt_rand(1, 10);
            $lineItems = [];
            $expectedTaxSum = '0.00';

            for ($j = 0; $j < $lineCount; $j++) {
                $dpp     = (string) mt_rand(1000, 500000);
                $rate    = (mt_rand(0, 1) === 1) ? '11.00' : '0.00';
                $taxRaw  = bcdiv(bcmul($dpp, $rate, 10), '100', 10);
                $taxAmt  = number_format((float) floor((float) $taxRaw), 2, '.', '');

                $lineItems[] = [
                    'line_subtotal'   => $dpp,
                    'discount_amount' => '0.00',
                    'tax_amount'      => $taxAmt,
                    'line_total'      => bcadd($dpp, $taxAmt, 2),
                ];

                $expectedTaxSum = bcadd($expectedTaxSum, $taxAmt, 2);
            }

            $result = $this->service->calculateGrandTotal($lineItems, '0.00');

            $this->assertEquals(
                $expectedTaxSum,
                $result['tax_total'],
                "Iteration {$i}: tax_total should equal sum of line tax_amounts. " .
                "Expected {$expectedTaxSum}, got {$result['tax_total']}. lineCount={$lineCount}"
            );
        }
    }
}
