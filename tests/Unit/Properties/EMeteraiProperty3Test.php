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
 * Property 3: E-Meterai Threshold Trigger
 *
 * if pre_total >= 5000000 then ematerai_fee == 10000, else ematerai_fee == 0
 *
 * Validates: Requirements 5.4, 5.5
 *
 * @group property-based
 */
class EMeteraiProperty3Test extends TestCase
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

        // Partial mock of InvoiceCalculationService so getEMeteraiThreshold() returns known value
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
     * Property 3: ematerai_fee == 0 when pre_total < threshold (50 cases)
     */
    public function test_ematerai_threshold_trigger_below(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $preTotal = (string) mt_rand(1000, 4999999);

            $lineItems = [[
                'line_subtotal'   => $preTotal,
                'discount_amount' => '0.00',
                'tax_amount'      => '0.00',
                'line_total'      => $preTotal,
            ]];

            $result = $this->service->calculateGrandTotal($lineItems, '0.00');

            $this->assertEquals(
                '0.00',
                $result['ematerai_fee'],
                "Iteration {$i} (below threshold): ematerai_fee should be 0 when pre_total={$preTotal} < {$this->threshold}"
            );
        }
    }

    /**
     * Property 3: ematerai_fee == 10000 when pre_total >= threshold (50 cases)
     */
    public function test_ematerai_threshold_trigger_above(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $preTotal = (string) mt_rand(5000000, 50000000);

            $lineItems = [[
                'line_subtotal'   => $preTotal,
                'discount_amount' => '0.00',
                'tax_amount'      => '0.00',
                'line_total'      => $preTotal,
            ]];

            $result = $this->service->calculateGrandTotal($lineItems, '0.00');

            $this->assertEquals(
                '10000.00',
                $result['ematerai_fee'],
                "Iteration {$i} (above threshold): ematerai_fee should be 10000 when pre_total={$preTotal} >= {$this->threshold}"
            );
        }
    }

    /**
     * Property 3: ematerai_fee == 10000 at exact threshold boundary
     */
    public function test_ematerai_threshold_trigger_exact_boundary(): void
    {
        $lineItems = [[
            'line_subtotal'   => '5000000.00',
            'discount_amount' => '0.00',
            'tax_amount'      => '0.00',
            'line_total'      => '5000000.00',
        ]];

        $result = $this->service->calculateGrandTotal($lineItems, '0.00');

        $this->assertEquals(
            '10000.00',
            $result['ematerai_fee'],
            'ematerai_fee should be 10000 when pre_total == threshold (5000000)'
        );
    }
}
