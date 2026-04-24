<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Unit;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Organization;
use App\Services\UnitConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnitTest extends TestCase
{
    use RefreshDatabase;

    private UnitConversionService $conversionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversionService = app(UnitConversionService::class);
    }

    // -----------------------------------------------------------------------
    // UNIT MODEL TESTS
    // -----------------------------------------------------------------------

    public function test_can_create_unit(): void
    {
        $unit = Unit::factory()->create([
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'type' => 'base',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('units', [
            'name' => 'Pieces',
            'symbol' => 'pcs',
            'type' => 'base',
            'is_active' => true,
        ]);

        $this->assertEquals('Pieces (pcs)', $unit->formatted_name);
    }

    public function test_unit_scope_active(): void
    {
        Unit::factory()->create(['is_active' => true]);
        Unit::factory()->create(['is_active' => false]);

        $activeUnits = Unit::active()->get();
        $this->assertCount(1, $activeUnits);
        $this->assertTrue($activeUnits->first()->is_active);
    }

    public function test_unit_scope_of_type(): void
    {
        Unit::factory()->create(['type' => 'weight']);
        Unit::factory()->create(['type' => 'volume']);
        Unit::factory()->create(['type' => 'base']);

        $weightUnits = Unit::ofType('weight')->get();
        $this->assertCount(1, $weightUnits);
        $this->assertEquals('weight', $weightUnits->first()->type);
    }

    // -----------------------------------------------------------------------
    // PRODUCT UNIT RELATIONSHIP TESTS
    // -----------------------------------------------------------------------

    public function test_product_can_have_multiple_units(): void
    {
        $product = Product::factory()->create();
        $boxUnit = Unit::factory()->create(['name' => 'Box', 'symbol' => 'box']);
        $pcsUnit = Unit::factory()->create(['name' => 'Pieces', 'symbol' => 'pcs']);

        // Create product-unit relationships
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $boxUnit->id,
            'conversion_to_base' => 50, // 1 box = 50 pcs
            'is_base_unit' => false,
            'is_default_purchase' => true,
            'is_default_sales' => false,
        ]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $pcsUnit->id,
            'conversion_to_base' => 1, // 1 pcs = 1 pcs (base unit)
            'is_base_unit' => true,
            'is_default_purchase' => false,
            'is_default_sales' => true,
        ]);

        $this->assertCount(2, $product->productUnits);
        $this->assertNotNull($product->default_purchase_unit);
        $this->assertNotNull($product->default_sales_unit);
        $this->assertEquals('Box', $product->default_purchase_unit->unit->name);
        $this->assertEquals('Pieces', $product->default_sales_unit->unit->name);
    }

    public function test_product_unit_conversion_methods(): void
    {
        $product = Product::factory()->create();
        $pcsUnit = Unit::factory()->create(['name' => 'Pieces']);

        $productUnit = ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $pcsUnit->id,
            'conversion_to_base' => 50, // 1 unit = 50 base units
            'is_base_unit' => false,
        ]);

        // Test conversion methods
        $this->assertEquals(100, $productUnit->convertToBase(2)); // 2 * 50 = 100
        $this->assertEquals(2, $productUnit->convertFromBase(100)); // 100 / 50 = 2
    }

    // -----------------------------------------------------------------------
    // UNIT CONVERSION SERVICE TESTS
    // -----------------------------------------------------------------------

    public function test_conversion_service_same_unit(): void
    {
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'conversion_to_base' => 1,
            'is_base_unit' => true,
        ]);

        $result = $this->conversionService->convert($product->id, 10, $unit->id, $unit->id);
        $this->assertEquals(10, $result);
    }

    public function test_conversion_service_different_units(): void
    {
        $product = Product::factory()->create();
        $boxUnit = Unit::factory()->create(['name' => 'Box']);
        $pcsUnit = Unit::factory()->create(['name' => 'Pieces']);

        // 1 Box = 50 Pieces
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $boxUnit->id,
            'conversion_to_base' => 50,
            'is_base_unit' => false,
        ]);

        // 1 Piece = 1 Piece (base unit)
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $pcsUnit->id,
            'conversion_to_base' => 1,
            'is_base_unit' => true,
        ]);

        // Convert 2 Box to Pieces: 2 * 50 / 1 = 100 Pieces
        $result = $this->conversionService->convert($product->id, 2, $boxUnit->id, $pcsUnit->id);
        $this->assertEquals(100, $result);

        // Convert 100 Pieces to Box: 100 * 1 / 50 = 2 Box
        $result = $this->conversionService->convert($product->id, 100, $pcsUnit->id, $boxUnit->id);
        $this->assertEquals(2, $result);
    }

    public function test_conversion_service_to_base_unit(): void
    {
        $product = Product::factory()->create();
        $boxUnit = Unit::factory()->create(['name' => 'Box']);
        $pcsUnit = Unit::factory()->create(['name' => 'Pieces']);

        $product->update(['base_unit_id' => $pcsUnit->id]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $boxUnit->id,
            'conversion_to_base' => 50,
            'is_base_unit' => false,
        ]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $pcsUnit->id,
            'conversion_to_base' => 1,
            'is_base_unit' => true,
        ]);

        // Convert 3 Box to base unit (Pieces): 3 * 50 = 150 Pieces
        $result = $this->conversionService->toBaseUnit($product->id, 3, $boxUnit->id);
        $this->assertEquals(150, $result);

        // Base unit to base unit should return same value
        $result = $this->conversionService->toBaseUnit($product->id, 100, $pcsUnit->id);
        $this->assertEquals(100, $result);
    }

    public function test_conversion_service_from_base_unit(): void
    {
        $product = Product::factory()->create();
        $boxUnit = Unit::factory()->create(['name' => 'Box']);
        $pcsUnit = Unit::factory()->create(['name' => 'Pieces']);

        $product->update(['base_unit_id' => $pcsUnit->id]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $boxUnit->id,
            'conversion_to_base' => 50,
            'is_base_unit' => false,
        ]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $pcsUnit->id,
            'conversion_to_base' => 1,
            'is_base_unit' => true,
        ]);

        // Convert 150 base units (Pieces) to Box: 150 / 50 = 3 Box
        $result = $this->conversionService->fromBaseUnit($product->id, 150, $boxUnit->id);
        $this->assertEquals(3, $result);

        // Base unit to base unit should return same value
        $result = $this->conversionService->fromBaseUnit($product->id, 100, $pcsUnit->id);
        $this->assertEquals(100, $result);
    }

    public function test_conversion_service_price_per_base_unit(): void
    {
        $product = Product::factory()->create();
        $boxUnit = Unit::factory()->create(['name' => 'Box']);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $boxUnit->id,
            'conversion_to_base' => 50, // 1 Box = 50 Pieces
            'is_base_unit' => false,
        ]);

        // Price: Rp 50,000 per Box
        // Price per base unit: Rp 50,000 / 50 = Rp 1,000 per Piece
        $pricePerBaseUnit = $this->conversionService->pricePerBaseUnit(50000, $product->id, $boxUnit->id);
        $this->assertEquals(1000, $pricePerBaseUnit);
    }

    public function test_conversion_service_get_available_units(): void
    {
        $product = Product::factory()->create();
        $boxUnit = Unit::factory()->create(['name' => 'Box', 'symbol' => 'box']);
        $pcsUnit = Unit::factory()->create(['name' => 'Pieces', 'symbol' => 'pcs']);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $boxUnit->id,
            'conversion_to_base' => 50,
            'is_base_unit' => false,
            'is_default_purchase' => true,
        ]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $pcsUnit->id,
            'conversion_to_base' => 1,
            'is_base_unit' => true,
            'is_default_sales' => true,
        ]);

        $availableUnits = $this->conversionService->getAvailableUnits($product->id);

        $this->assertCount(2, $availableUnits);
        
        $boxUnitData = $availableUnits->firstWhere('unit_name', 'Box');
        $this->assertEquals('box', $boxUnitData['unit_symbol']);
        $this->assertEquals(50, $boxUnitData['conversion_to_base']);
        $this->assertTrue($boxUnitData['is_default_purchase']);
        $this->assertFalse($boxUnitData['is_base_unit']);

        $pcsUnitData = $availableUnits->firstWhere('unit_name', 'Pieces');
        $this->assertEquals('pcs', $pcsUnitData['unit_symbol']);
        $this->assertEquals(1, $pcsUnitData['conversion_to_base']);
        $this->assertTrue($pcsUnitData['is_default_sales']);
        $this->assertTrue($pcsUnitData['is_base_unit']);
    }

    public function test_conversion_service_is_unit_available(): void
    {
        $product = Product::factory()->create();
        $availableUnit = Unit::factory()->create();
        $unavailableUnit = Unit::factory()->create();

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $availableUnit->id,
            'conversion_to_base' => 1,
        ]);

        $this->assertTrue($this->conversionService->isUnitAvailable($product->id, $availableUnit->id));
        $this->assertFalse($this->conversionService->isUnitAvailable($product->id, $unavailableUnit->id));
    }

    // -----------------------------------------------------------------------
    // ERROR HANDLING TESTS
    // -----------------------------------------------------------------------

    public function test_conversion_service_throws_exception_for_missing_unit(): void
    {
        $product = Product::factory()->create();
        $unit1 = Unit::factory()->create();
        $unit2 = Unit::factory()->create();

        // Only create one product unit
        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $unit1->id,
            'conversion_to_base' => 1,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Target unit');

        $this->conversionService->convert($product->id, 10, $unit1->id, $unit2->id);
    }

    public function test_conversion_service_throws_exception_for_zero_conversion(): void
    {
        $product = Product::factory()->create();
        $unit = Unit::factory()->create();

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $unit->id,
            'conversion_to_base' => 0, // Invalid conversion ratio
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid conversion ratio');

        $this->conversionService->fromBaseUnit($product->id, 100, $unit->id);
    }

    // -----------------------------------------------------------------------
    // PRODUCT MODEL UNIT METHODS TESTS
    // -----------------------------------------------------------------------

    public function test_product_convert_unit_method(): void
    {
        $product = Product::factory()->create();
        $boxUnit = Unit::factory()->create();
        $pcsUnit = Unit::factory()->create();

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $boxUnit->id,
            'conversion_to_base' => 50,
        ]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $pcsUnit->id,
            'conversion_to_base' => 1,
        ]);

        // Test product's convertUnit method
        $result = $product->convertUnit(2, $boxUnit->id, $pcsUnit->id);
        $this->assertEquals(100, $result); // 2 box * 50 / 1 = 100 pcs
    }

    public function test_product_to_base_unit_method(): void
    {
        $product = Product::factory()->create();
        $boxUnit = Unit::factory()->create();
        $pcsUnit = Unit::factory()->create();

        $product->update(['base_unit_id' => $pcsUnit->id]);

        ProductUnit::create([
            'product_id' => $product->id,
            'unit_id' => $boxUnit->id,
            'conversion_to_base' => 50,
        ]);

        // Test product's toBaseUnit method
        $result = $product->toBaseUnit(3, $boxUnit->id);
        $this->assertEquals(150, $result); // 3 box * 50 = 150 pcs

        // Same unit as base unit
        $result = $product->toBaseUnit(100, $pcsUnit->id);
        $this->assertEquals(100, $result);
    }
}