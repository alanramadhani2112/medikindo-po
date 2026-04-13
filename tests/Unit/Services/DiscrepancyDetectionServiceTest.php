<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\AuditService;
use App\Services\BCMathCalculatorService;
use App\Services\DiscrepancyDetectionService;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;

class DiscrepancyDetectionServiceTest extends TestCase
{
    private DiscrepancyDetectionService $discrepancyDetector;
    private BCMathCalculatorService $calculator;
    private AuditService $auditService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calculator = new BCMathCalculatorService();
        $this->auditService = Mockery::mock(AuditService::class);
        $this->auditService->shouldReceive('log')->byDefault();
        
        $this->discrepancyDetector = new DiscrepancyDetectionService(
            $this->calculator,
            $this->auditService
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createMockPurchaseOrder(array $items): PurchaseOrder
    {
        $po = Mockery::mock(PurchaseOrder::class)->makePartial();
        $po->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $po->shouldReceive('getAttribute')->with('po_number')->andReturn('PO-TEST-001');
        
        $poItems = new Collection();
        foreach ($items as $itemData) {
            $item = Mockery::mock(PurchaseOrderItem::class)->makePartial();
            $item->shouldReceive('getAttribute')->with('product_id')->andReturn($itemData['product_id'] ?? 1);
            $item->shouldReceive('getAttribute')->with('quantity')->andReturn($itemData['quantity']);
            $item->shouldReceive('getAttribute')->with('unit_price')->andReturn($itemData['unit_price']);
            
            $product = Mockery::mock(Product::class)->makePartial();
            $product->shouldReceive('getAttribute')->with('name')->andReturn($itemData['product_name'] ?? 'Test Product');
            $item->shouldReceive('getAttribute')->with('product')->andReturn($product);
            
            // Allow direct property access
            $item->product_id = $itemData['product_id'] ?? 1;
            $item->quantity = $itemData['quantity'];
            $item->unit_price = $itemData['unit_price'];
            $item->product = $product;
            
            $poItems->push($item);
        }
        
        $po->shouldReceive('getAttribute')->with('items')->andReturn($poItems);
        $po->items = $poItems;
        $po->id = 1;
        $po->po_number = 'PO-TEST-001';
        
        return $po;
    }

    public function test_it_calculates_expected_total_from_po()
    {
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '10.000', 'unit_price' => '100.00'],
            ['quantity' => '5.000', 'unit_price' => '200.00'],
        ]);
        
        $expectedTotal = $this->discrepancyDetector->calculateExpectedTotal($po);
        
        // 10 × 100 + 5 × 200 = 1000 + 1000 = 2000
        $this->assertEquals('2000.00', $expectedTotal);
    }

    public function test_it_detects_no_discrepancy_when_amounts_match()
    {
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '10.000', 'unit_price' => '100.00'],
        ]);
        
        $result = $this->discrepancyDetector->detect('1000.00', $po);
        
        $this->assertEquals('1000.00', $result['invoice_total']);
        $this->assertEquals('1000.00', $result['expected_total']);
        $this->assertEquals('0.00', $result['variance_amount']);
        $this->assertEquals('0.00', $result['variance_percentage']);
        $this->assertFalse($result['discrepancy_detected']);
        $this->assertFalse($result['requires_approval']);
    }

    public function test_it_detects_discrepancy_when_percentage_exceeds_threshold()
    {
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '10.000', 'unit_price' => '100.00'], // Expected: 1000.00
        ]);
        
        // Invoice: 1015.00, Expected: 1000.00, Variance: 15.00 (1.5%)
        $result = $this->discrepancyDetector->detect('1015.00', $po);
        
        $this->assertEquals('15.00', $result['variance_amount']);
        // BCMath with scale=2: 15/1000*100 = 1.50
        $this->assertEqualsWithDelta(1.50, (float)$result['variance_percentage'], 0.01);
        $this->assertTrue($result['discrepancy_detected']);
        $this->assertTrue($result['requires_approval']);
    }

    public function test_it_detects_discrepancy_when_amount_exceeds_threshold()
    {
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '100.000', 'unit_price' => '10000.00'], // Expected: 1,000,000.00
        ]);
        
        // Invoice: 1,011,000.00, Expected: 1,000,000.00, Variance: 11,000.00 (1.1%)
        // Percentage > 1% AND amount > 10,000
        $result = $this->discrepancyDetector->detect('1011000.00', $po);
        
        $this->assertEquals('11000.00', $result['variance_amount']);
        $this->assertTrue($result['discrepancy_detected']);
    }

    public function test_it_does_not_flag_small_discrepancies()
    {
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '10.000', 'unit_price' => '100.00'], // Expected: 1000.00
        ]);
        
        // Invoice: 1005.00, Expected: 1000.00, Variance: 5.00 (0.5%)
        // Below both thresholds
        $result = $this->discrepancyDetector->detect('1005.00', $po);
        
        $this->assertEquals('5.00', $result['variance_amount']);
        $this->assertEquals('0.50', $result['variance_percentage']);
        $this->assertFalse($result['discrepancy_detected']);
    }

    public function test_it_handles_negative_variance()
    {
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '10.000', 'unit_price' => '100.00'], // Expected: 1000.00
        ]);
        
        // Invoice: 980.00, Expected: 1000.00, Variance: -20.00 (2%)
        $result = $this->discrepancyDetector->detect('980.00', $po);
        
        $this->assertEquals('-20.00', $result['variance_amount']);
        $this->assertEquals('2.00', $result['variance_percentage']);
        $this->assertTrue($result['discrepancy_detected']);
    }

    public function test_it_calculates_variance_percentage_correctly()
    {
        $percentage = $this->discrepancyDetector->calculateVariancePercentage('15.00', '1000.00');
        
        // 15 / 1000 × 100 = 1.5%
        $this->assertEqualsWithDelta(1.50, (float)$percentage, 0.01);
    }

    public function test_it_handles_zero_expected_total()
    {
        $percentage = $this->discrepancyDetector->calculateVariancePercentage('10.00', '0.00');
        
        $this->assertEquals('0.00', $percentage);
    }

    public function test_it_determines_discrepancy_severity_none()
    {
        $severity = $this->discrepancyDetector->getDiscrepancySeverity('5.00', '0.50');
        
        $this->assertEquals('none', $severity);
    }

    public function test_it_determines_discrepancy_severity_low()
    {
        $severity = $this->discrepancyDetector->getDiscrepancySeverity('15000.00', '1.50');
        
        $this->assertEquals('low', $severity);
    }

    public function test_it_determines_discrepancy_severity_medium()
    {
        $severity = $this->discrepancyDetector->getDiscrepancySeverity('30000.00', '2.50');
        
        $this->assertEquals('medium', $severity);
    }

    public function test_it_determines_discrepancy_severity_high()
    {
        $severity = $this->discrepancyDetector->getDiscrepancySeverity('60000.00', '6.00');
        
        $this->assertEquals('high', $severity);
    }

    public function test_it_checks_if_within_acceptable_range()
    {
        $this->assertTrue($this->discrepancyDetector->isWithinAcceptableRange('5.00', '0.50'));
        $this->assertFalse($this->discrepancyDetector->isWithinAcceptableRange('15.00', '1.50'));
    }

    public function test_it_detects_with_breakdown()
    {
        $po = $this->createMockPurchaseOrder([
            ['product_id' => 1, 'product_name' => 'Product A', 'quantity' => '10.000', 'unit_price' => '100.00'],
            ['product_id' => 2, 'product_name' => 'Product B', 'quantity' => '5.000', 'unit_price' => '200.00'],
        ]);
        
        $result = $this->discrepancyDetector->detectWithBreakdown('2000.00', $po);
        
        $this->assertArrayHasKey('line_item_breakdown', $result);
        $this->assertCount(2, $result['line_item_breakdown']);
        $this->assertEquals(2, $result['line_item_count']);
        
        $this->assertEquals('Product A', $result['line_item_breakdown'][0]['product_name']);
        $this->assertEquals('1000.00', $result['line_item_breakdown'][0]['line_total']);
        
        $this->assertEquals('Product B', $result['line_item_breakdown'][1]['product_name']);
        $this->assertEquals('1000.00', $result['line_item_breakdown'][1]['line_total']);
    }

    public function test_it_formats_discrepancy_for_display()
    {
        $discrepancy = [
            'invoice_total' => '1015.00',
            'expected_total' => '1000.00',
            'variance_amount' => '15.00',
            'variance_percentage' => '1.50',
            'requires_approval' => true,
        ];
        
        $formatted = $this->discrepancyDetector->formatForDisplay($discrepancy);
        
        $this->assertStringContainsString('Rp', $formatted['invoice_total']);
        $this->assertStringContainsString('Rp', $formatted['expected_total']);
        $this->assertStringContainsString('%', $formatted['variance_percentage']);
        $this->assertEquals('low', $formatted['severity']);
        $this->assertTrue($formatted['requires_approval']);
        $this->assertNotEmpty($formatted['message']);
    }

    public function test_it_validates_inputs()
    {
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '10.000', 'unit_price' => '100.00'],
        ]);
        
        // Should not throw exception
        $this->discrepancyDetector->validateInputs('1000.00', $po);
        
        $this->assertTrue(true); // If we get here, validation passed
    }

    public function test_it_rejects_non_numeric_invoice_total()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice total must be a numeric value');
        
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '10.000', 'unit_price' => '100.00'],
        ]);
        
        $this->discrepancyDetector->validateInputs('invalid', $po);
    }

    public function test_it_rejects_negative_invoice_total()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice total must be non-negative');
        
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '10.000', 'unit_price' => '100.00'],
        ]);
        
        $this->discrepancyDetector->validateInputs('-1000.00', $po);
    }

    public function test_it_rejects_empty_purchase_order()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Purchase order must have at least one line item');
        
        $po = $this->createMockPurchaseOrder([]);
        
        $this->discrepancyDetector->validateInputs('1000.00', $po);
    }

    public function test_it_handles_realistic_pharmacy_scenario()
    {
        // Realistic scenario: Large pharmacy order
        $po = $this->createMockPurchaseOrder([
            ['quantity' => '100.000', 'unit_price' => '5000.00'],  // 500,000
            ['quantity' => '50.000', 'unit_price' => '15000.00'],  // 750,000
            ['quantity' => '200.000', 'unit_price' => '2500.00'],  // 500,000
        ]);
        // Expected total: 1,750,000
        
        // Invoice with 2% variance (35,000 over)
        $result = $this->discrepancyDetector->detect('1785000.00', $po);
        
        $this->assertEquals('1750000.00', $result['expected_total']);
        $this->assertEquals('35000.00', $result['variance_amount']);
        $this->assertEquals('2.00', $result['variance_percentage']);
        $this->assertTrue($result['discrepancy_detected']);
        $this->assertEquals('medium', $this->discrepancyDetector->getDiscrepancySeverity(
            $result['variance_amount'],
            $result['variance_percentage']
        ));
    }
}
