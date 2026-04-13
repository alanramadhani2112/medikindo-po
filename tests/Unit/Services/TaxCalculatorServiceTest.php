<?php

namespace Tests\Unit\Services;

use App\Services\AuditService;
use App\Services\BCMathCalculatorService;
use App\Services\TaxCalculatorService;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class TaxCalculatorServiceTest extends TestCase
{
    private TaxCalculatorService $taxCalculator;
    private BCMathCalculatorService $calculator;
    private AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calculator = new BCMathCalculatorService();
        $this->auditService = Mockery::mock(AuditService::class);
        $this->auditService->shouldReceive('log')->byDefault();
        
        $this->taxCalculator = new TaxCalculatorService($this->calculator, $this->auditService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_calculates_tax_correctly()
    {
        // 11% of 100.00 = 11.00
        $result = $this->taxCalculator->calculate('100.00', '11.00');
        
        $this->assertEquals('11.00', $result['tax_rate']);
        $this->assertEquals('11.00', $result['tax_amount']);
    }

    public function test_it_handles_zero_tax_rate()
    {
        $result = $this->taxCalculator->calculate('100.00', '0.00');
        
        $this->assertEquals('0.00', $result['tax_rate']);
        $this->assertEquals('0.00', $result['tax_amount']);
    }

    public function test_it_handles_null_tax_rate()
    {
        $result = $this->taxCalculator->calculate('100.00', null);
        
        $this->assertEquals('0.00', $result['tax_rate']);
        $this->assertEquals('0.00', $result['tax_amount']);
    }

    public function test_it_calculates_fractional_tax_rate()
    {
        // 11.5% of 100.00 = 11.50
        $result = $this->taxCalculator->calculate('100.00', '11.50');
        
        $this->assertEquals('11.50', $result['tax_rate']);
        $this->assertEquals('11.50', $result['tax_amount']);
    }

    public function test_it_applies_rounding_to_tax_amount()
    {
        // 10% of 33.33 = 3.333, should round to 3.33
        $result = $this->taxCalculator->calculate('33.33', '10.00');
        
        $this->assertEquals('3.33', $result['tax_amount']);
    }

    public function test_it_rejects_negative_taxable_amount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Taxable amount must be non-negative');
        
        $this->taxCalculator->calculate('-100.00', '11.00');
    }

    public function test_it_rejects_negative_tax_rate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tax rate must be non-negative');
        
        $this->taxCalculator->calculate('100.00', '-5.00');
    }

    public function test_it_rejects_tax_rate_over_100()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tax rate cannot exceed 100%');
        
        $this->taxCalculator->calculate('100.00', '101.00');
    }

    public function test_it_rejects_non_numeric_taxable_amount()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Taxable amount must be a numeric value');
        
        $this->taxCalculator->calculate('invalid', '11.00');
    }

    public function test_it_rejects_non_numeric_tax_rate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tax rate must be a numeric value');
        
        $this->taxCalculator->calculate('100.00', 'invalid');
    }

    public function test_it_calculates_tax_on_discounted_amount()
    {
        // Subtotal: 100.00, Discount: 10.00, Taxable: 90.00, Tax (11%): 9.90
        $result = $this->taxCalculator->calculateOnDiscountedAmount('100.00', '10.00', '11.00');
        
        $this->assertEquals('90.00', $result['taxable_amount']);
        $this->assertEquals('11.00', $result['tax_rate']);
        $this->assertEquals('9.90', $result['tax_amount']);
    }

    public function test_it_handles_zero_discount()
    {
        $result = $this->taxCalculator->calculateOnDiscountedAmount('100.00', '0.00', '11.00');
        
        $this->assertEquals('100.00', $result['taxable_amount']);
        $this->assertEquals('11.00', $result['tax_amount']);
    }

    public function test_it_rejects_discount_exceeding_subtotal()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Taxable amount cannot be negative');
        
        $this->taxCalculator->calculateOnDiscountedAmount('100.00', '150.00', '11.00');
    }

    public function test_it_calculates_total_with_tax()
    {
        $total = $this->taxCalculator->calculateTotal('100.00', '11.00');
        
        $this->assertEquals('111.00', $total);
    }

    public function test_it_calculates_tax_inclusive_amount()
    {
        // 100.00 + 11% = 111.00
        $result = $this->taxCalculator->calculateTaxInclusive('100.00', '11.00');
        
        $this->assertEquals('111.00', $result['tax_inclusive_amount']);
        $this->assertEquals('11.00', $result['tax_amount']);
    }

    public function test_it_calculates_tax_exclusive_amount()
    {
        // 111.00 / 1.11 = 100.00
        $result = $this->taxCalculator->calculateTaxExclusive('111.00', '11.00');
        
        $this->assertEquals('100.00', $result['tax_exclusive_amount']);
        $this->assertEquals('11.00', $result['tax_amount']);
    }

    public function test_it_handles_zero_tax_rate_in_exclusive_calculation()
    {
        $result = $this->taxCalculator->calculateTaxExclusive('100.00', '0.00');
        
        $this->assertEquals('100.00', $result['tax_exclusive_amount']);
        $this->assertEquals('0.00', $result['tax_amount']);
    }

    public function test_it_validates_tax_rate()
    {
        $this->assertTrue($this->taxCalculator->validateTaxRate('11.00'));
        $this->assertTrue($this->taxCalculator->validateTaxRate('0.00'));
        $this->assertTrue($this->taxCalculator->validateTaxRate('100.00'));
    }

    public function test_it_rejects_invalid_tax_rate_in_validation()
    {
        $this->expectException(InvalidArgumentException::class);
        
        $this->taxCalculator->validateTaxRate('101.00');
    }

    public function test_it_calculates_indonesian_ppn()
    {
        // Indonesian PPN (VAT) is 11%
        $result = $this->taxCalculator->calculate('1000000.00', '11.00');
        
        $this->assertEquals('11.00', $result['tax_rate']);
        $this->assertEquals('110000.00', $result['tax_amount']);
    }

    public function test_it_calculates_realistic_pharmacy_invoice()
    {
        // Subtotal: Rp 5,000,000
        // Discount: Rp 250,000 (5%)
        // Taxable: Rp 4,750,000
        // Tax (11%): Rp 522,500
        $result = $this->taxCalculator->calculateOnDiscountedAmount('5000000.00', '250000.00', '11.00');
        
        $this->assertEquals('4750000.00', $result['taxable_amount']);
        $this->assertEquals('522500.00', $result['tax_amount']);
    }

    public function test_it_handles_small_amounts()
    {
        // 11% of 0.01 = 0.0011, rounds to 0.00
        $result = $this->taxCalculator->calculate('0.01', '11.00');
        
        $this->assertEquals('0.00', $result['tax_amount']);
    }

    public function test_it_handles_large_amounts()
    {
        // 11% of 999,999.99
        $result = $this->taxCalculator->calculate('999999.99', '11.00');
        
        $this->assertEquals('109999.99', $result['tax_amount']);
    }

    public function test_tax_inclusive_and_exclusive_are_reversible()
    {
        // Start with tax-exclusive amount
        $taxExclusive = '100.00';
        $taxRate = '11.00';
        
        // Convert to tax-inclusive
        $inclusive = $this->taxCalculator->calculateTaxInclusive($taxExclusive, $taxRate);
        
        // Convert back to tax-exclusive
        $exclusive = $this->taxCalculator->calculateTaxExclusive($inclusive['tax_inclusive_amount'], $taxRate);
        
        // Should get back the original amount
        $this->assertEquals($taxExclusive, $exclusive['tax_exclusive_amount']);
    }

    public function test_it_calculates_zero_tax_on_zero_amount()
    {
        $result = $this->taxCalculator->calculate('0.00', '11.00');
        
        $this->assertEquals('0.00', $result['tax_amount']);
    }

    public function test_it_calculates_hundred_percent_tax()
    {
        // 100% tax doubles the amount
        $result = $this->taxCalculator->calculate('100.00', '100.00');
        
        $this->assertEquals('100.00', $result['tax_amount']);
    }
}
