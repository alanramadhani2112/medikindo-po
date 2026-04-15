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
 * Property 2: Tax Floor Rounding
 *
 * tax_amount == floor(dpp * rate / 100)
 * tax_amount <= dpp * rate / 100
 *
 * Validates: Requirements 5.2
 *
 * @group property-based
 */
class InvoiceCalculationProperty2Test extends TestCase
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

        $this->service = new InvoiceCalculationService(
            $calculator,
            $discountValidator,
            $taxCalculator,
            $auditService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Property 2: tax_amount == floor(dpp * rate / 100) AND tax_amount <= dpp * rate / 100
     */
    public function test_tax_floor_rounding_property(): void
    {
        $rate = '11.00';

        for ($i = 0; $i < 100; $i++) {
            $dpp = (string) mt_rand(1000, 10000000);

            $taxAmount = $this->service->calculateTaxFloor($dpp, $rate);

            // Compute exact value with high precision
            $exact = bcdiv(bcmul($dpp, $rate, 10), '100', 10);

            // Property A: tax_amount == floor(exact)
            $expectedFloor = number_format((float) floor((float) $exact), 2, '.', '');

            $this->assertEquals(
                $expectedFloor,
                $taxAmount,
                "Iteration {$i}: tax_amount should equal floor(dpp * rate / 100). " .
                "dpp={$dpp}, rate={$rate}, exact={$exact}, expected={$expectedFloor}, got={$taxAmount}"
            );

            // Property B: tax_amount <= dpp * rate / 100
            $this->assertLessThanOrEqual(
                (float) $exact,
                (float) $taxAmount,
                "Iteration {$i}: tax_amount must be <= dpp * rate / 100. " .
                "dpp={$dpp}, exact={$exact}, taxAmount={$taxAmount}"
            );
        }
    }
}
