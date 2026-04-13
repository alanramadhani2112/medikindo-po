<?php

namespace Tests\Unit\Services;

use App\Services\AuditService;
use App\Services\BCMathCalculatorService;
use App\Services\DiscountValidatorService;
use App\Services\InvoiceCalculationService;
use App\Services\TaxCalculatorService;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class InvoiceCalculationServiceTest extends TestCase
{
    private InvoiceCalculationService $invoiceCalculator;
    private BCMathCalculatorService $calculator;
    private DiscountValidatorService $discountValidator;
    private TaxCalculatorService $taxCalculator;
    private AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calculator = new BCMathCalculatorService();
        $this->auditService = Mockery::mock(AuditService::class);
        $this->auditService->shouldReceive('log')->byDefault();
        
        $this->discountValidator = new DiscountValidatorService($this->calculator, $this->auditService);
        $this->taxCalculator = new TaxCalculatorService($this->calculator, $this->auditService);
        $this->invoiceCalculator = new InvoiceCalculationService(
            $this->calculator,
            $this->discountValidator,
            $this->taxCalculator,
            $this->auditService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_calculates_line_item_without_discount_or_tax()
    {
        // 10 units @ 100.00 = 1000.00
        $result = $this->invoiceCalculator->calculateLineItem('10.000', '100.00');
        
        $this->assertEquals('10.000', $result['quantity']);
        $this->assertEquals('100.00', $result['unit_price']);
        $this->assertEquals('1000.00', $result['line_subtotal']);
        $this->assertEquals('0.00', $result['discount_amount']);
        $this->assertEquals('1000.00', $result['taxable_amount']);
        $this->assertEquals('0.00', $result['tax_amount']);
        $this->assertEquals('1000.00', $result['line_total']);
    }

    public function test_it_calculates_line_item_with_discount_percentage()
    {
        // 10 units @ 100.00 = 1000.00, 10% discount = 100.00, total = 900.00
        $result = $this->invoiceCalculator->calculateLineItem('10.000', '100.00', '10.00');
        
        $this->assertEquals('1000.00', $result['line_subtotal']);
        $this->assertEquals('10.00', $result['discount_percentage']);
        $this->assertEquals('100.00', $result['discount_amount']);
        $this->assertEquals('900.00', $result['taxable_amount']);
        $this->assertEquals('900.00', $result['line_total']);
    }

    public function test_it_calculates_line_item_with_tax()
    {
        // 10 units @ 100.00 = 1000.00, 11% tax = 110.00, total = 1110.00
        $result = $this->invoiceCalculator->calculateLineItem('10.000', '100.00', null, null, '11.00');
        
        $this->assertEquals('1000.00', $result['line_subtotal']);
        $this->assertEquals('1000.00', $result['taxable_amount']);
        $this->assertEquals('11.00', $result['tax_rate']);
        $this->assertEquals('110.00', $result['tax_amount']);
        $this->assertEquals('1110.00', $result['line_total']);
    }

    public function test_it_calculates_line_item_with_discount_and_tax()
    {
        // 10 units @ 100.00 = 1000.00
        // 10% discount = 100.00, taxable = 900.00
        // 11% tax on 900.00 = 99.00
        // Total = 999.00
        $result = $this->invoiceCalculator->calculateLineItem('10.000', '100.00', '10.00', null, '11.00');
        
        $this->assertEquals('1000.00', $result['line_subtotal']);
        $this->assertEquals('100.00', $result['discount_amount']);
        $this->assertEquals('900.00', $result['taxable_amount']);
        $this->assertEquals('99.00', $result['tax_amount']);
        $this->assertEquals('999.00', $result['line_total']);
    }

    public function test_it_rejects_negative_quantity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantity must be a non-negative numeric value');
        
        $this->invoiceCalculator->calculateLineItem('-10.000', '100.00');
    }

    public function test_it_rejects_negative_unit_price()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit price must be a non-negative numeric value');
        
        $this->invoiceCalculator->calculateLineItem('10.000', '-100.00');
    }

    public function test_it_calculates_invoice_totals_from_multiple_line_items()
    {
        $lineItems = [
            $this->invoiceCalculator->calculateLineItem('10.000', '100.00', '10.00', null, '11.00'),
            $this->invoiceCalculator->calculateLineItem('5.000', '200.00', '5.00', null, '11.00'),
        ];
        
        $totals = $this->invoiceCalculator->calculateInvoiceTotals($lineItems);
        
        // Line 1: 1000.00 subtotal, 100.00 discount, 900.00 taxable, 99.00 tax, 999.00 total
        // Line 2: 1000.00 subtotal, 50.00 discount, 950.00 taxable, 104.50 tax, 1054.50 total
        // Invoice: 2000.00 subtotal, 150.00 discount, 203.50 tax, 2053.50 total
        
        $this->assertEquals('2000.00', $totals['subtotal_amount']);
        $this->assertEquals('150.00', $totals['discount_amount']);
        $this->assertEquals('203.50', $totals['tax_amount']);
        $this->assertEquals('2053.50', $totals['total_amount']);
        $this->assertEquals(2, $totals['line_count']);
    }

    public function test_it_handles_empty_line_items()
    {
        $totals = $this->invoiceCalculator->calculateInvoiceTotals([]);
        
        $this->assertEquals('0.00', $totals['subtotal_amount']);
        $this->assertEquals('0.00', $totals['discount_amount']);
        $this->assertEquals('0.00', $totals['tax_amount']);
        $this->assertEquals('0.00', $totals['total_amount']);
        $this->assertEquals(0, $totals['line_count']);
    }

    public function test_it_verifies_tolerance_check_passes()
    {
        $lineItems = [
            ['line_total' => '100.00'],
            ['line_total' => '200.00'],
            ['line_total' => '300.00'],
        ];
        
        $result = $this->invoiceCalculator->verifyToleranceCheck($lineItems, '600.00');
        
        $this->assertTrue($result['passed']);
        $this->assertTrue($result['within_tolerance']);
        $this->assertEquals('600.00', $result['calculated_total']);
        $this->assertEquals('600.00', $result['expected_total']);
        $this->assertEquals('0.00', $result['difference']);
    }

    public function test_it_verifies_tolerance_check_passes_within_tolerance()
    {
        $lineItems = [
            ['line_total' => '100.00'],
            ['line_total' => '200.00'],
            ['line_total' => '300.01'], // 0.01 difference
        ];
        
        $result = $this->invoiceCalculator->verifyToleranceCheck($lineItems, '600.00');
        
        $this->assertTrue($result['passed']);
        $this->assertTrue($result['within_tolerance']);
        $this->assertEquals('0.01', $result['absolute_difference']);
    }

    public function test_it_verifies_tolerance_check_fails_outside_tolerance()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Line item totals (600.02) do not match invoice total (600.00)');
        
        $lineItems = [
            ['line_total' => '100.00'],
            ['line_total' => '200.00'],
            ['line_total' => '300.02'], // 0.02 difference - exceeds tolerance
        ];
        
        $this->invoiceCalculator->verifyToleranceCheck($lineItems, '600.00');
    }

    public function test_it_calculates_complete_invoice()
    {
        $lineItemsData = [
            [
                'quantity' => '10.000',
                'unit_price' => '100.00',
                'discount_percentage' => '10.00',
                'tax_rate' => '11.00',
            ],
            [
                'quantity' => '5.000',
                'unit_price' => '200.00',
                'discount_percentage' => '5.00',
                'tax_rate' => '11.00',
            ],
        ];
        
        $result = $this->invoiceCalculator->calculateCompleteInvoice($lineItemsData);
        
        $this->assertCount(2, $result['line_items']);
        $this->assertEquals('2000.00', $result['invoice_totals']['subtotal_amount']);
        $this->assertEquals('2053.50', $result['invoice_totals']['total_amount']);
        $this->assertTrue($result['tolerance_check']['passed']);
    }

    public function test_it_recalculates_line_item()
    {
        $existingData = [
            'quantity' => '10.000',
            'unit_price' => '100.00',
            'discount_percentage' => '10.00',
            'tax_rate' => '11.00',
        ];
        
        $result = $this->invoiceCalculator->recalculateLineItem($existingData);
        
        $this->assertEquals('1000.00', $result['line_subtotal']);
        $this->assertEquals('999.00', $result['line_total']);
    }

    public function test_it_verifies_calculation_integrity()
    {
        $lineItems = [
            [
                'line_subtotal' => '1000.00',
                'discount_amount' => '100.00',
                'tax_amount' => '99.00',
                'line_total' => '999.00',
            ],
            [
                'line_subtotal' => '1000.00',
                'discount_amount' => '50.00',
                'tax_amount' => '104.50',
                'line_total' => '1054.50',
            ],
        ];
        
        $invoiceTotals = [
            'subtotal_amount' => '2000.00',
            'discount_amount' => '150.00',
            'tax_amount' => '203.50',
            'total_amount' => '2053.50',
        ];
        
        $result = $this->invoiceCalculator->verifyCalculationIntegrity($lineItems, $invoiceTotals);
        
        $this->assertTrue($result['passed']);
        $this->assertTrue($result['checks']['subtotal_match']);
        $this->assertTrue($result['checks']['discount_match']);
        $this->assertTrue($result['checks']['tax_match']);
        $this->assertTrue($result['checks']['total_match']);
    }

    public function test_it_detects_calculation_integrity_mismatch()
    {
        $lineItems = [
            [
                'line_subtotal' => '1000.00',
                'discount_amount' => '100.00',
                'tax_amount' => '99.00',
                'line_total' => '999.00',
            ],
        ];
        
        $invoiceTotals = [
            'subtotal_amount' => '1000.00',
            'discount_amount' => '100.00',
            'tax_amount' => '99.00',
            'total_amount' => '1000.00', // Wrong total
        ];
        
        $result = $this->invoiceCalculator->verifyCalculationIntegrity($lineItems, $invoiceTotals);
        
        $this->assertFalse($result['passed']);
        $this->assertFalse($result['checks']['total_match']);
    }

    public function test_it_handles_fractional_quantities()
    {
        // 2.5 units @ 100.00 = 250.00
        $result = $this->invoiceCalculator->calculateLineItem('2.500', '100.00');
        
        $this->assertEquals('2.500', $result['quantity']);
        $this->assertEquals('250.00', $result['line_subtotal']);
    }

    public function test_it_handles_realistic_pharmacy_invoice()
    {
        // Realistic pharmacy invoice with multiple items
        $lineItemsData = [
            [
                'quantity' => '100.000', // 100 tablets
                'unit_price' => '5000.00',
                'discount_percentage' => '5.00',
                'tax_rate' => '11.00',
            ],
            [
                'quantity' => '50.000', // 50 bottles
                'unit_price' => '15000.00',
                'discount_percentage' => '10.00',
                'tax_rate' => '11.00',
            ],
        ];
        
        $result = $this->invoiceCalculator->calculateCompleteInvoice($lineItemsData);
        
        // Line 1: 500,000 subtotal, 25,000 discount, 475,000 taxable, 52,250 tax, 527,250 total
        // Line 2: 750,000 subtotal, 75,000 discount, 675,000 taxable, 74,250 tax, 749,250 total
        // Invoice: 1,250,000 subtotal, 100,000 discount, 126,500 tax, 1,276,500 total
        
        $this->assertEquals('1250000.00', $result['invoice_totals']['subtotal_amount']);
        $this->assertEquals('100000.00', $result['invoice_totals']['discount_amount']);
        $this->assertEquals('126500.00', $result['invoice_totals']['tax_amount']);
        $this->assertEquals('1276500.00', $result['invoice_totals']['total_amount']);
        $this->assertTrue($result['tolerance_check']['passed']);
    }

    public function test_it_handles_zero_quantity()
    {
        $result = $this->invoiceCalculator->calculateLineItem('0.000', '100.00');
        
        $this->assertEquals('0.00', $result['line_subtotal']);
        $this->assertEquals('0.00', $result['line_total']);
    }

    public function test_it_handles_zero_unit_price()
    {
        $result = $this->invoiceCalculator->calculateLineItem('10.000', '0.00');
        
        $this->assertEquals('0.00', $result['line_subtotal']);
        $this->assertEquals('0.00', $result['line_total']);
    }
}
