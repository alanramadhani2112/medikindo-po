<?php

namespace Tests\Unit\Properties;

use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceLineItem;
use App\Models\Product;
use App\Services\AuditService;
use App\Services\BCMathCalculatorService;
use App\Services\MarginProtectionService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Property 5: Margin Protection Blocks ISSUED Transition
 *
 * MarginProtectionService::check() returns non-empty violations
 * when any line item has selling_price < cost_price.
 *
 * Validates: Requirements 18.1
 *
 * @group property-based
 */
class MarginProtectionProperty5Test extends TestCase
{
    private MarginProtectionService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $auditService = Mockery::mock(AuditService::class);
        $auditService->shouldReceive('log')->byDefault();

        $calculator    = new BCMathCalculatorService();
        $this->service = new MarginProtectionService($calculator, $auditService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeInvoiceWithLineItem(
        string $sellingPrice,
        string $costPrice,
        string $productName = 'Test Product'
    ): CustomerInvoice {
        $product = Mockery::mock(Product::class)->makePartial();
        $product->name = $productName;

        $lineItem = Mockery::mock(CustomerInvoiceLineItem::class)->makePartial();
        $lineItem->id             = mt_rand(1, 9999);
        $lineItem->unit_price     = $sellingPrice;
        $lineItem->cost_price     = $costPrice;
        $lineItem->product_name   = $productName;
        $lineItem->shouldReceive('getAttribute')->with('unit_price')->andReturn($sellingPrice);
        $lineItem->shouldReceive('getAttribute')->with('cost_price')->andReturn($costPrice);
        $lineItem->shouldReceive('getAttribute')->with('product')->andReturn($product);
        $lineItem->shouldReceive('getAttribute')->with('product_name')->andReturn($productName);
        $lineItem->shouldReceive('getAttribute')->with('id')->andReturn($lineItem->id);

        $collection = new Collection([$lineItem]);

        $invoice = Mockery::mock(CustomerInvoice::class)->makePartial();
        $invoice->shouldReceive('loadMissing')->andReturnSelf();
        $invoice->shouldReceive('getAttribute')->with('lineItems')->andReturn($collection);

        return $invoice;
    }

    /**
     * Property 5: violations array is non-empty when selling_price < cost_price
     */
    public function test_margin_violations_detected_when_selling_below_cost(): void
    {
        for ($i = 0; $i < 100; $i++) {
            // Generate cost_price > selling_price (guaranteed violation)
            $costPrice    = mt_rand(10000, 500000);
            $sellingPrice = mt_rand(1000, $costPrice - 1); // strictly less than cost

            $invoice = $this->makeInvoiceWithLineItem(
                (string) $sellingPrice,
                (string) $costPrice,
                'Product-' . $i
            );

            $violations = $this->service->check($invoice);

            $this->assertNotEmpty(
                $violations,
                "Iteration {$i}: violations should not be empty when " .
                "selling_price ({$sellingPrice}) < cost_price ({$costPrice})"
            );

            // Verify the violation contains correct product info
            $violation = $violations[0];

            $this->assertArrayHasKey('product_name', $violation, "Iteration {$i}: violation missing product_name");
            $this->assertArrayHasKey('selling_price', $violation, "Iteration {$i}: violation missing selling_price");
            $this->assertArrayHasKey('cost_price', $violation, "Iteration {$i}: violation missing cost_price");
            $this->assertArrayHasKey('diff', $violation, "Iteration {$i}: violation missing diff");

            // diff should be negative (selling < cost)
            $this->assertLessThan(
                0,
                (float) $violation['diff'],
                "Iteration {$i}: violation diff should be negative when selling < cost"
            );
        }
    }

    public function test_no_violations_when_selling_price_equals_cost(): void
    {
        $price   = (string) mt_rand(10000, 500000);
        $invoice = $this->makeInvoiceWithLineItem($price, $price);

        $violations = $this->service->check($invoice);

        $this->assertEmpty($violations, 'No violations when selling_price == cost_price');
    }

    public function test_no_violations_when_selling_price_above_cost(): void
    {
        $costPrice    = mt_rand(10000, 100000);
        $sellingPrice = $costPrice + mt_rand(1, 50000);

        $invoice = $this->makeInvoiceWithLineItem(
            (string) $sellingPrice,
            (string) $costPrice
        );

        $violations = $this->service->check($invoice);

        $this->assertEmpty(
            $violations,
            "No violations when selling_price ({$sellingPrice}) > cost_price ({$costPrice})"
        );
    }
}
