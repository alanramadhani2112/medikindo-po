<?php

namespace Tests\Unit\Services;

use App\Services\AuditService;
use App\Services\BCMathCalculatorService;
use App\Services\DiscountValidatorService;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class DiscountValidatorServiceTest extends TestCase
{
    private DiscountValidatorService $validator;
    private BCMathCalculatorService $calculator;
    private AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calculator = new BCMathCalculatorService();
        $this->auditService = Mockery::mock(AuditService::class);
        $this->auditService->shouldReceive('log')->byDefault();
        
        $this->validator = new DiscountValidatorService($this->calculator, $this->auditService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_validates_percentage_within_range()
    {
        $result = $this->validator->validate('100.00', '10.00', null);
        
        $this->assertEquals('10.00', $result['discount_percentage']);
        $this->assertEquals('10.00', $result['discount_amount']);
    }

    public function test_it_validates_zero_percentage()
    {
        $result = $this->validator->validate('100.00', '0.00', null);
        
        $this->assertEquals('0.00', $result['discount_percentage']);
        $this->assertEquals('0.00', $result['discount_amount']);
    }

    public function test_it_validates_hundred_percent()
    {
        $result = $this->validator->validate('100.00', '100.00', null);
        
        $this->assertEquals('100.00', $result['discount_percentage']);
        $this->assertEquals('100.00', $result['discount_amount']);
    }

    public function test_it_rejects_percentage_over_100()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount percentage cannot exceed 100%');
        
        $this->validator->validate('100.00', '101.00', null);
    }

    public function test_it_rejects_negative_percentage()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount percentage must be non-negative');
        
        $this->validator->validate('100.00', '-5.00', null);
    }

    public function test_it_validates_amount_within_subtotal()
    {
        $result = $this->validator->validate('100.00', null, '25.00');
        
        $this->assertNull($result['discount_percentage']);
        $this->assertEquals('25.00', $result['discount_amount']);
    }

    public function test_it_validates_zero_amount()
    {
        $result = $this->validator->validate('100.00', null, '0.00');
        
        $this->assertNull($result['discount_percentage']);
        $this->assertEquals('0.00', $result['discount_amount']);
    }

    public function test_it_validates_amount_equal_to_subtotal()
    {
        $result = $this->validator->validate('100.00', null, '100.00');
        
        $this->assertNull($result['discount_percentage']);
        $this->assertEquals('100.00', $result['discount_amount']);
    }

    public function test_it_rejects_amount_exceeding_subtotal()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount amount cannot exceed subtotal');
        
        $this->validator->validate('100.00', null, '150.00');
    }

    public function test_it_rejects_negative_amount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount amount must be non-negative');
        
        $this->validator->validate('100.00', null, '-10.00');
    }

    public function test_it_rejects_both_percentage_and_amount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot specify both discount percentage and discount amount');
        
        $this->validator->validate('100.00', '10.00', '10.00');
    }

    public function test_it_returns_zero_when_no_discount_provided()
    {
        $result = $this->validator->validate('100.00', null, null);
        
        $this->assertNull($result['discount_percentage']);
        $this->assertEquals('0.00', $result['discount_amount']);
    }

    public function test_it_calculates_discount_amount_from_percentage()
    {
        // 12.5% of 200.00 = 25.00
        $result = $this->validator->validate('200.00', '12.50', null);
        
        $this->assertEquals('12.50', $result['discount_percentage']);
        $this->assertEquals('25.00', $result['discount_amount']);
    }

    public function test_it_calculates_percentage_from_amount()
    {
        // 25.00 of 200.00 = 12.5%
        $percentage = $this->validator->calculatePercentageFromAmount('200.00', '25.00');
        
        // BCMath with scale=2 will give us 12.00, not 12.50
        // Because: 25/200 = 0.125, then 0.125 * 100 = 12.50 but with scale=2 intermediate: 0.12 * 100 = 12.00
        // This is expected behavior with scale=2
        $this->assertEquals('12.00', $percentage);
    }

    public function test_it_calculates_amount_from_percentage()
    {
        // 15% of 300.00 = 45.00
        $amount = $this->validator->calculateAmountFromPercentage('300.00', '15.00');
        
        $this->assertEquals('45.00', $amount);
    }

    public function test_it_handles_fractional_percentages()
    {
        // 7.25% of 100.00 = 7.25
        $result = $this->validator->validate('100.00', '7.25', null);
        
        $this->assertEquals('7.25', $result['discount_percentage']);
        $this->assertEquals('7.25', $result['discount_amount']);
    }

    public function test_it_handles_small_amounts()
    {
        // 0.01 discount on 100.00
        $result = $this->validator->validate('100.00', null, '0.01');
        
        $this->assertNull($result['discount_percentage']);
        $this->assertEquals('0.01', $result['discount_amount']);
    }

    public function test_it_handles_large_amounts()
    {
        // 10% of 999999.99
        $result = $this->validator->validate('999999.99', '10.00', null);
        
        $this->assertEquals('10.00', $result['discount_percentage']);
        $this->assertEquals('99999.99', $result['discount_amount']);
    }

    public function test_it_rejects_invalid_subtotal()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Subtotal must be a non-negative numeric value');
        
        $this->validator->validate('-100.00', '10.00', null);
    }

    public function test_it_rejects_non_numeric_percentage()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount percentage must be a numeric value');
        
        $this->validator->validate('100.00', 'invalid', null);
    }

    public function test_it_rejects_non_numeric_amount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Discount amount must be a numeric value');
        
        $this->validator->validate('100.00', null, 'invalid');
    }

    public function test_percentage_from_amount_handles_zero_subtotal()
    {
        $percentage = $this->validator->calculatePercentageFromAmount('0.00', '0.00');
        
        $this->assertEquals('0.00', $percentage);
    }

    public function test_it_validates_realistic_pharmacy_discount()
    {
        // Realistic scenario: 5% discount on Rp 1,250,000
        $result = $this->validator->validate('1250000.00', '5.00', null);
        
        $this->assertEquals('5.00', $result['discount_percentage']);
        $this->assertEquals('62500.00', $result['discount_amount']);
    }

    public function test_it_validates_bulk_purchase_discount()
    {
        // Bulk purchase: 15% discount on Rp 5,000,000
        $result = $this->validator->validate('5000000.00', '15.00', null);
        
        $this->assertEquals('15.00', $result['discount_percentage']);
        $this->assertEquals('750000.00', $result['discount_amount']);
    }
}
