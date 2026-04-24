<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = app(InventoryService::class);
    }

    // -----------------------------------------------------------------------
    // INVENTORY INDEX TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_inventory_index(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product = Product::factory()->create();
        $inventoryItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->create();

        $response = $this->get(route('inventory.index'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.index');
        $response->assertViewHas(['inventoryItems', 'stats']);
        $response->assertSee($product->name);
    }

    public function test_inventory_index_shows_correct_stats(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        // Create different types of inventory items
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Normal stock
        InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product1)
            ->withQuantities(100, 0)
            ->create();

        // Low stock
        InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product2)
            ->lowStock()
            ->create();

        // Expiring soon
        InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product1)
            ->expiringSoon()
            ->create();

        $response = $this->get(route('inventory.index'));

        $response->assertStatus(200);
        $stats = $response->viewData('stats');

        $this->assertEquals(2, $stats['total_products']); // 2 unique products
        $this->assertGreaterThan(0, $stats['total_stock']);
        $this->assertEquals(1, $stats['low_stock_count']);
        $this->assertEquals(1, $stats['expiring_soon_count']);
    }

    public function test_can_filter_inventory_by_status(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product = Product::factory()->create();

        // Create low stock item
        $lowStockItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->lowStock()
            ->create();

        // Create normal stock item
        $normalItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->withQuantities(100, 0)
            ->create();

        // Test low stock filter
        $response = $this->get(route('inventory.index', ['status' => 'low_stock']));
        $response->assertStatus(200);
        $items = $response->viewData('inventoryItems');
        $this->assertTrue($items->contains($lowStockItem));
        $this->assertFalse($items->contains($normalItem));
    }

    public function test_can_search_inventory_by_product(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product1 = Product::factory()->create(['name' => 'Paracetamol 500mg']);
        $product2 = Product::factory()->create(['name' => 'Amoxicillin 250mg']);

        $item1 = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product1)
            ->create();

        $item2 = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product2)
            ->create();

        // Search by product name
        $response = $this->get(route('inventory.index', ['search' => 'Paracetamol']));
        $response->assertStatus(200);
        $items = $response->viewData('inventoryItems');
        $this->assertTrue($items->contains($item1));
        $this->assertFalse($items->contains($item2));
    }

    // -----------------------------------------------------------------------
    // INVENTORY DETAIL TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_inventory_detail(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product = Product::factory()->create();
        $inventoryItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->create();

        $response = $this->get(route('inventory.show', $product));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.show');
        $response->assertViewHas(['product', 'inventoryItems', 'totalAvailable']);
        $response->assertSee($product->name);
        $response->assertSee($inventoryItem->batch_no);
    }

    public function test_inventory_detail_shows_correct_total_available(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product = Product::factory()->create();

        // Create multiple batches
        InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->withQuantities(100, 10) // 90 available
            ->create();

        InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->withQuantities(50, 5) // 45 available
            ->create();

        $response = $this->get(route('inventory.show', $product));

        $response->assertStatus(200);
        $totalAvailable = $response->viewData('totalAvailable');
        $this->assertEquals(135, $totalAvailable); // 90 + 45
    }

    // -----------------------------------------------------------------------
    // STOCK MOVEMENT TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_stock_movements(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product = Product::factory()->create();
        $inventoryItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->create();

        $movement = InventoryMovement::factory()
            ->forInventoryItem($inventoryItem)
            ->stockIn(50)
            ->create();

        $response = $this->get(route('inventory.movements'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.movements');
        $response->assertViewHas(['movements', 'products']);
        $response->assertSee($product->name);
    }

    public function test_can_filter_movements_by_product(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $item1 = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product1)
            ->create();

        $item2 = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product2)
            ->create();

        $movement1 = InventoryMovement::factory()
            ->forInventoryItem($item1)
            ->create();

        $movement2 = InventoryMovement::factory()
            ->forInventoryItem($item2)
            ->create();

        // Filter by product 1
        $response = $this->get(route('inventory.movements', ['product_id' => $product1->id]));
        $response->assertStatus(200);
        $movements = $response->viewData('movements');
        $this->assertTrue($movements->contains($movement1));
        $this->assertFalse($movements->contains($movement2));
    }

    public function test_can_filter_movements_by_type(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $inventoryItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->create();

        $inMovement = InventoryMovement::factory()
            ->forInventoryItem($inventoryItem)
            ->stockIn()
            ->create();

        $outMovement = InventoryMovement::factory()
            ->forInventoryItem($inventoryItem)
            ->stockOut()
            ->create();

        // Filter by stock in movements
        $response = $this->get(route('inventory.movements', ['movement_type' => 'in']));
        $response->assertStatus(200);
        $movements = $response->viewData('movements');
        $this->assertTrue($movements->contains($inMovement));
        $this->assertFalse($movements->contains($outMovement));
    }

    // -----------------------------------------------------------------------
    // LOW STOCK ALERTS TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_low_stock_alerts(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product = Product::factory()->create();
        $lowStockItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->lowStock()
            ->create();

        $response = $this->get(route('inventory.low-stock'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.low-stock');
        $response->assertViewHas('lowStockItems');
        $response->assertSee($product->name);
    }

    public function test_low_stock_alerts_only_show_low_stock_items(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Low stock item
        $lowStockItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product1)
            ->lowStock()
            ->create();

        // Normal stock item
        $normalItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product2)
            ->withQuantities(100, 0)
            ->create();

        $response = $this->get(route('inventory.low-stock'));

        $response->assertStatus(200);
        $lowStockItems = $response->viewData('lowStockItems');
        $this->assertTrue($lowStockItems->contains($lowStockItem));
        $this->assertFalse($lowStockItems->contains($normalItem));
    }

    // -----------------------------------------------------------------------
    // EXPIRING ITEMS TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_expiring_items(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product = Product::factory()->create();
        $expiringItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->expiringSoon()
            ->create();

        $response = $this->get(route('inventory.expiring'));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.expiring');
        $response->assertViewHas(['expiringItems', 'expiredItems']);
        $response->assertSee($product->name);
    }

    public function test_expiring_items_separates_expiring_and_expired(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        // Expiring soon item
        $expiringItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product1)
            ->expiringSoon()
            ->create();

        // Expired item
        $expiredItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product2)
            ->expired()
            ->create();

        $response = $this->get(route('inventory.expiring'));

        $response->assertStatus(200);
        $expiringItems = $response->viewData('expiringItems');
        $expiredItems = $response->viewData('expiredItems');

        $this->assertTrue($expiringItems->contains($expiringItem));
        $this->assertFalse($expiringItems->contains($expiredItem));
        $this->assertTrue($expiredItems->contains($expiredItem));
        $this->assertFalse($expiredItems->contains($expiringItem));
    }

    // -----------------------------------------------------------------------
    // STOCK ADJUSTMENT TESTS
    // -----------------------------------------------------------------------

    public function test_can_view_stock_adjustment_form(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $inventoryItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->create();

        $response = $this->get(route('inventory.adjust-form', $inventoryItem));

        $response->assertStatus(200);
        $response->assertViewIs('inventory.adjust');
        $response->assertViewHas('inventoryItem');
        $response->assertSee($inventoryItem->product->name);
    }

    public function test_can_adjust_stock_positive(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $inventoryItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->withQuantities(50, 0)
            ->create();

        $response = $this->post(route('inventory.adjust', $inventoryItem), [
            'quantity_change' => 25,
            'notes' => 'Stock count adjustment'
        ]);

        $response->assertRedirect(route('inventory.show', $inventoryItem->product_id));
        $response->assertSessionHas('success');

        // Verify stock was adjusted
        $inventoryItem->refresh();
        $this->assertEquals(75, $inventoryItem->quantity_on_hand);

        // Verify movement was recorded
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => InventoryMovement::TYPE_ADJUSTMENT,
            'quantity' => 25,
            'notes' => 'Stock count adjustment'
        ]);
    }

    public function test_can_adjust_stock_negative(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $inventoryItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->withQuantities(50, 0)
            ->create();

        $response = $this->post(route('inventory.adjust', $inventoryItem), [
            'quantity_change' => -10,
            'notes' => 'Damaged stock removal'
        ]);

        $response->assertRedirect(route('inventory.show', $inventoryItem->product_id));
        $response->assertSessionHas('success');

        // Verify stock was adjusted
        $inventoryItem->refresh();
        $this->assertEquals(40, $inventoryItem->quantity_on_hand);

        // Verify movement was recorded
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => InventoryMovement::TYPE_ADJUSTMENT,
            'quantity' => -10,
            'notes' => 'Damaged stock removal'
        ]);
    }

    public function test_stock_adjustment_validates_required_fields(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $inventoryItem = InventoryItem::factory()
            ->forOrganization($organization)
            ->create();

        // Missing quantity_change
        $response = $this->post(route('inventory.adjust', $inventoryItem), [
            'notes' => 'Test adjustment'
        ]);
        $response->assertSessionHasErrors(['quantity_change']);

        // Missing notes
        $response = $this->post(route('inventory.adjust', $inventoryItem), [
            'quantity_change' => 10
        ]);
        $response->assertSessionHasErrors(['notes']);

        // Zero quantity change
        $response = $this->post(route('inventory.adjust', $inventoryItem), [
            'quantity_change' => 0,
            'notes' => 'Test adjustment'
        ]);
        $response->assertSessionHasErrors(['quantity_change']);
    }

    // -----------------------------------------------------------------------
    // INVENTORY SERVICE TESTS
    // -----------------------------------------------------------------------

    public function test_inventory_service_can_add_stock(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create();
        $user = $this->createHealthcareUser($organization);

        $inventoryItem = $this->inventoryService->addStock(
            organizationId: $organization->id,
            productId: $product->id,
            batchNo: 'BATCH-001',
            expiryDate: '2025-12-31',
            quantity: 100,
            unitCost: 50.00,
            referenceType: 'GoodsReceipt',
            referenceId: 1,
            createdBy: $user->id,
            location: 'A1-01'
        );

        $this->assertDatabaseHas('inventory_items', [
            'organization_id' => $organization->id,
            'product_id' => $product->id,
            'batch_no' => 'BATCH-001',
            'quantity_on_hand' => 100,
            'unit_cost' => 50.00,
            'location' => 'A1-01'
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $inventoryItem->id,
            'movement_type' => InventoryMovement::TYPE_IN,
            'quantity' => 100,
            'reference_type' => 'GoodsReceipt',
            'reference_id' => 1
        ]);
    }

    public function test_inventory_service_can_reduce_stock_fefo(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create();
        $user = $this->createHealthcareUser($organization);

        // Create inventory items with different expiry dates (both in the future)
        $item1 = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->withQuantities(50, 0)
            ->state(['expiry_date' => now()->addDays(30)]) // Earlier expiry (30 days)
            ->create();

        $item2 = InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->withQuantities(50, 0)
            ->state(['expiry_date' => now()->addDays(90)]) // Later expiry (90 days)
            ->create();

        // Reduce 30 units (should come from item1 first - FEFO)
        $movements = $this->inventoryService->reduceStock(
            organizationId: $organization->id,
            productId: $product->id,
            quantity: 30,
            referenceType: 'CustomerInvoice',
            referenceId: 1,
            createdBy: $user->id
        );

        // Verify FEFO logic - item1 should be reduced first
        $item1->refresh();
        $item2->refresh();

        $this->assertEquals(20, $item1->quantity_on_hand); // 50 - 30
        $this->assertEquals(50, $item2->quantity_on_hand); // Unchanged

        // Verify movement was recorded
        $this->assertCount(1, $movements);
        $this->assertEquals($item1->id, $movements[0]->inventory_item_id);
        $this->assertEquals(-30, $movements[0]->quantity);
    }

    public function test_inventory_service_prevents_overselling(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create();
        $user = $this->createHealthcareUser($organization);

        // Create inventory item with limited stock
        InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->withQuantities(20, 0)
            ->create();

        // Try to reduce more than available
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient stock');

        $this->inventoryService->reduceStock(
            organizationId: $organization->id,
            productId: $product->id,
            quantity: 30, // More than available (20)
            referenceType: 'CustomerInvoice',
            referenceId: 1,
            createdBy: $user->id
        );
    }

    public function test_inventory_service_gets_available_stock(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create();

        // Create multiple inventory items
        InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->withQuantities(100, 10) // 90 available
            ->create();

        InventoryItem::factory()
            ->forOrganization($organization)
            ->forProduct($product)
            ->withQuantities(50, 5) // 45 available
            ->create();

        $availableStock = $this->inventoryService->getAvailableStock(
            $organization->id,
            $product->id
        );

        $this->assertEquals(135, $availableStock); // 90 + 45
    }

    // -----------------------------------------------------------------------
    // MULTI-TENANT SECURITY TESTS
    // -----------------------------------------------------------------------

    public function test_inventory_is_organization_scoped(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $user = $this->actingAsHealthcareUser($org1);

        $product = Product::factory()->create();

        // Create inventory for both organizations
        $item1 = InventoryItem::factory()
            ->forOrganization($org1)
            ->forProduct($product)
            ->create();

        $item2 = InventoryItem::factory()
            ->forOrganization($org2)
            ->forProduct($product)
            ->create();

        $response = $this->get(route('inventory.index'));
        $response->assertStatus(200);

        $items = $response->viewData('inventoryItems');
        $itemIds = $items->pluck('id')->toArray();

        $this->assertContains($item1->id, $itemIds);
        $this->assertNotContains($item2->id, $itemIds);
    }

    public function test_cannot_adjust_other_organization_inventory(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();

        $user = $this->actingAsHealthcareUser($org1);

        $inventoryItem = InventoryItem::factory()
            ->forOrganization($org2) // Different organization
            ->create();

        $response = $this->get(route('inventory.adjust-form', $inventoryItem));
        
        // Should be handled by policy/middleware - might return 403 or 404
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    // -----------------------------------------------------------------------
    // INVENTORY MODEL TESTS
    // -----------------------------------------------------------------------

    public function test_inventory_item_calculates_available_quantity(): void
    {
        $inventoryItem = InventoryItem::factory()
            ->withQuantities(100, 25)
            ->make();

        $this->assertEquals(75, $inventoryItem->quantity_available);
    }

    public function test_inventory_item_detects_low_stock(): void
    {
        $lowStockItem = InventoryItem::factory()
            ->withQuantities(5, 0)
            ->make();

        $normalStockItem = InventoryItem::factory()
            ->withQuantities(50, 0)
            ->make();

        $this->assertTrue($lowStockItem->isLowStock());
        $this->assertFalse($normalStockItem->isLowStock());
    }

    public function test_inventory_item_detects_expiring_soon(): void
    {
        $expiringItem = InventoryItem::factory()
            ->state(['expiry_date' => now()->addDays(30)])
            ->make();

        $notExpiringItem = InventoryItem::factory()
            ->state(['expiry_date' => now()->addDays(90)])
            ->make();

        $this->assertTrue($expiringItem->isExpiringSoon());
        $this->assertFalse($notExpiringItem->isExpiringSoon());
    }

    public function test_inventory_item_detects_expired(): void
    {
        $expiredItem = InventoryItem::factory()
            ->state(['expiry_date' => now()->subDays(10)])
            ->make();

        $validItem = InventoryItem::factory()
            ->state(['expiry_date' => now()->addDays(30)])
            ->make();

        $this->assertTrue($expiredItem->isExpired());
        $this->assertFalse($validItem->isExpired());
    }

    // -----------------------------------------------------------------------
    // AUTHORIZATION TESTS
    // -----------------------------------------------------------------------

    public function test_healthcare_users_can_access_inventory(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $response = $this->get(route('inventory.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_inventory(): void
    {
        $user = $this->actingAsSuperAdmin();

        $response = $this->get(route('inventory.index'));
        $response->assertStatus(200);
    }

    // -----------------------------------------------------------------------
    // PAGINATION TESTS
    // -----------------------------------------------------------------------

    public function test_inventory_index_pagination(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        // Create more than 20 inventory items (default pagination limit)
        InventoryItem::factory()
            ->count(25)
            ->forOrganization($organization)
            ->create();

        $response = $this->get(route('inventory.index'));
        $response->assertStatus(200);

        $items = $response->viewData('inventoryItems');
        $this->assertEquals(20, $items->count()); // Should be paginated
        $this->assertTrue($items->hasPages());
    }
}