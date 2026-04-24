<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\Organization;
use App\Services\PriceListService;
use App\Exceptions\PriceListNotFoundException;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PriceListTest extends TestCase
{
    use RefreshDatabase;

    private PriceListService $priceListService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->priceListService = app(PriceListService::class);
    }

    // -----------------------------------------------------------------------
    // PRICE LIST MODEL TESTS
    // -----------------------------------------------------------------------

    public function test_can_create_price_list(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);

        $priceList = PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 12000,
            'effective_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('price_lists', [
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 12000,
            'is_active' => true,
        ]);

        $this->assertEquals($organization->id, $priceList->organization_id);
        $this->assertEquals($product->id, $priceList->product_id);
    }

    public function test_price_list_relationships(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create();

        $priceList = PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
        ]);

        $this->assertInstanceOf(Organization::class, $priceList->organization);
        $this->assertInstanceOf(Product::class, $priceList->product);
        $this->assertEquals($organization->name, $priceList->organization->name);
        $this->assertEquals($product->name, $priceList->product->name);
    }

    // -----------------------------------------------------------------------
    // PRICE LIST SCOPE TESTS
    // -----------------------------------------------------------------------

    public function test_scope_active_for_date(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create();
        $today = Carbon::today();

        // Active price list (effective and not expired)
        $activePriceList = PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 15000,
            'effective_date' => $today->copy()->subDays(5),
            'expiry_date' => $today->copy()->addDays(10),
            'is_active' => true,
        ]);

        // Inactive price list (different effective date to avoid unique constraint)
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 14000,
            'effective_date' => $today->copy()->subDays(10), // Different effective date
            'expiry_date' => $today->copy()->addDays(10),
            'is_active' => false,
        ]);

        // Future price list (not yet effective)
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 16000,
            'effective_date' => $today->copy()->addDays(5),
            'expiry_date' => $today->copy()->addDays(20),
            'is_active' => true,
        ]);

        // Expired price list
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 13000,
            'effective_date' => $today->copy()->subDays(20),
            'expiry_date' => $today->copy()->subDays(5),
            'is_active' => true,
        ]);

        $activePriceLists = PriceList::activeForDate($today)->get();

        $this->assertCount(1, $activePriceLists);
        $this->assertEquals($activePriceList->id, $activePriceLists->first()->id);
        $this->assertEquals(15000, $activePriceLists->first()->selling_price);
    }

    public function test_scope_active_for_date_with_null_expiry(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create();
        $today = Carbon::today();

        // Price list with no expiry date (permanent)
        $permanentPriceList = PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 18000,
            'effective_date' => $today->copy()->subDays(10),
            'expiry_date' => null, // No expiry
            'is_active' => true,
        ]);

        $activePriceLists = PriceList::activeForDate($today)->get();

        $this->assertCount(1, $activePriceLists);
        $this->assertEquals($permanentPriceList->id, $activePriceLists->first()->id);
    }

    // -----------------------------------------------------------------------
    // PRICE LIST SERVICE TESTS
    // -----------------------------------------------------------------------

    public function test_price_lookup_with_customer_specific_price(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);

        // Customer-specific price list
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 12000,
            'effective_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        $this->assertEquals('12000.00', $price);
    }

    public function test_price_lookup_fallback_to_product_price(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 15000]);

        // No customer-specific price list
        $price = $this->priceListService->lookup($organization->id, $product->id);

        $this->assertEquals('15000.00', $price);
    }

    public function test_price_lookup_prioritizes_latest_effective_date(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);

        // Older price list
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 11000,
            'effective_date' => now()->subDays(10),
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Newer price list (should take priority)
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 13000,
            'effective_date' => now()->subDays(3),
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        $this->assertEquals('13000.00', $price);
    }

    public function test_price_lookup_ignores_inactive_price_lists(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);

        // Inactive price list (should be ignored)
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 20000,
            'effective_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(30),
            'is_active' => false,
        ]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        // Should fallback to product price
        $this->assertEquals('10000.00', $price);
    }

    public function test_price_lookup_ignores_future_price_lists(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);

        // Future price list (not yet effective)
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 25000,
            'effective_date' => now()->addDays(5),
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        // Should fallback to product price
        $this->assertEquals('10000.00', $price);
    }

    public function test_price_lookup_ignores_expired_price_lists(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);

        // Expired price list
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 30000,
            'effective_date' => now()->subDays(20),
            'expiry_date' => now()->subDays(5),
            'is_active' => true,
        ]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        // Should fallback to product price
        $this->assertEquals('10000.00', $price);
    }

    public function test_price_lookup_with_zero_selling_price(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 0]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        $this->assertEquals('0.00', $price);
    }

    public function test_price_lookup_organization_isolation(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);

        // Price list for org1 only
        PriceList::factory()->create([
            'organization_id' => $org1->id,
            'product_id' => $product->id,
            'selling_price' => 15000,
            'effective_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Org1 should get custom price
        $priceOrg1 = $this->priceListService->lookup($org1->id, $product->id);
        $this->assertEquals('15000.00', $priceOrg1);

        // Org2 should get fallback price
        $priceOrg2 = $this->priceListService->lookup($org2->id, $product->id);
        $this->assertEquals('10000.00', $priceOrg2);
    }

    // -----------------------------------------------------------------------
    // EDGE CASES AND BUSINESS SCENARIOS
    // -----------------------------------------------------------------------

    public function test_price_list_effective_on_exact_date(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);
        $today = Carbon::today();

        // Price list effective exactly today
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 12000,
            'effective_date' => $today,
            'expiry_date' => $today->copy()->addDays(30),
            'is_active' => true,
        ]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        $this->assertEquals('12000.00', $price);
    }

    public function test_price_list_expires_on_exact_date(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000]);
        $today = Carbon::today();

        // Price list expires exactly today (should still be active)
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 12000,
            'effective_date' => $today->copy()->subDays(10),
            'expiry_date' => $today,
            'is_active' => true,
        ]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        $this->assertEquals('12000.00', $price);
    }

    public function test_multiple_products_different_prices(): void
    {
        $organization = Organization::factory()->create();
        $product1 = Product::factory()->create(['selling_price' => 10000]);
        $product2 = Product::factory()->create(['selling_price' => 20000]);

        // Custom price for product1 only
        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product1->id,
            'selling_price' => 15000,
            'effective_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        $price1 = $this->priceListService->lookup($organization->id, $product1->id);
        $price2 = $this->priceListService->lookup($organization->id, $product2->id);

        $this->assertEquals('15000.00', $price1); // Custom price
        $this->assertEquals('20000.00', $price2); // Fallback to product price
    }

    public function test_price_list_decimal_precision(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create(['selling_price' => 10000.50]);

        PriceList::factory()->create([
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'selling_price' => 12345.67,
            'effective_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        $price = $this->priceListService->lookup($organization->id, $product->id);

        $this->assertEquals('12345.67', $price);
    }
}