<?php

namespace Tests\Feature;

use App\Models\GoodsReceipt;
use App\Models\Organization;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\GoodsReceiptService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GoodsReceiptTest extends TestCase
{
    // -----------------------------------------------------------------------
    // helpers
    // -----------------------------------------------------------------------

    private function makeApprovedPOWithItems(): array
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->forSupplier($supplier)->create();
        
        $user = User::factory()->healthcareUser()->forOrganization($organization)->create();
        
        $po = PurchaseOrder::factory()
            ->approved()
            ->forOrganization($organization)
            ->forSupplier($supplier)
            ->createdBy($user)
            ->create();
            
        $po->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);
        
        $po->recalculateTotals();
        $po->save();

        return [$organization, $supplier, $product, $po, $user];
    }

    // -----------------------------------------------------------------------
    // CREATE GR
    // -----------------------------------------------------------------------

    public function test_healthcare_user_can_confirm_receipt(): void
    {
        [$organization, $supplier, $product, $po, $user] = $this->makeApprovedPOWithItems();
        $this->actingAsUser($user);

        Storage::fake('public');
        $photo = UploadedFile::fake()->image('delivery.jpg');

        $response = $this->postJson('/api/goods-receipts', [
            'purchase_order_id' => $po->id,
            'delivery_order_number' => 'DO-001',
            'delivery_photo' => $photo,
            'items' => [
                [
                    'purchase_order_item_id' => $po->items->first()->id,
                    'product_id' => $product->id,
                    'quantity_received' => 5,
                    'batch_no' => 'BATCH001',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'condition' => 'Good',
                ],
            ],
        ]);
        
        if ($response->status() !== 201) {
            dump($response->json());
        }
        
        $response->assertStatus(201)
            ->assertJsonPath('data.status', GoodsReceipt::STATUS_PARTIAL);
    }

    public function test_cannot_confirm_receipt_with_expired_product(): void
    {
        [$organization, $supplier, $product, $po, $user] = $this->makeApprovedPOWithItems();
        $this->actingAsUser($user);

        Storage::fake('public');
        $photo = UploadedFile::fake()->image('delivery.jpg');

        $this->postJson('/api/goods-receipts', [
            'purchase_order_id' => $po->id,
            'delivery_order_number' => 'DO-001',
            'delivery_photo' => $photo,
            'items' => [
                [
                    'purchase_order_item_id' => $po->items->first()->id,
                    'product_id' => $product->id,
                    'quantity_received' => 5,
                    'batch_no' => 'BATCH001',
                    'expiry_date' => now()->subDay()->format('Y-m-d'), // Expired
                    'condition' => 'Good',
                ],
            ],
        ])->assertStatus(422)
            ->assertJsonStructure(['errors' => ['items.0.expiry_date']]);
    }

    public function test_cannot_receive_more_than_ordered(): void
    {
        [$organization, $supplier, $product, $po, $user] = $this->makeApprovedPOWithItems();
        $this->actingAsUser($user);

        Storage::fake('public');
        $photo = UploadedFile::fake()->image('delivery.jpg');

        $this->postJson('/api/goods-receipts', [
            'purchase_order_id' => $po->id,
            'delivery_order_number' => 'DO-001',
            'delivery_photo' => $photo,
            'items' => [
                [
                    'purchase_order_item_id' => $po->items->first()->id,
                    'product_id' => $product->id,
                    'quantity_received' => 15, // More than ordered (10)
                    'batch_no' => 'BATCH001',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'condition' => 'Good',
                ],
            ],
        ])->assertStatus(422);
    }

    public function test_partial_then_complete_receipt(): void
    {
        [$organization, $supplier, $product, $po, $user] = $this->makeApprovedPOWithItems();
        $this->actingAsUser($user);

        Storage::fake('public');
        $photo1 = UploadedFile::fake()->image('delivery1.jpg');
        $photo2 = UploadedFile::fake()->image('delivery2.jpg');

        // First partial receipt
        $this->postJson('/api/goods-receipts', [
            'purchase_order_id' => $po->id,
            'delivery_order_number' => 'DO-001',
            'delivery_photo' => $photo1,
            'items' => [
                [
                    'purchase_order_item_id' => $po->items->first()->id,
                    'product_id' => $product->id,
                    'quantity_received' => 6,
                    'batch_no' => 'BATCH001',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'condition' => 'Good',
                ],
            ],
        ])->assertStatus(201)
            ->assertJsonPath('data.status', GoodsReceipt::STATUS_PARTIAL);

        // Complete the receipt
        $gr = GoodsReceipt::where('purchase_order_id', $po->id)->first();
        
        $this->postJson('/api/goods-receipts', [
            'goods_receipt_id' => $gr->id,
            'delivery_order_number' => 'DO-002',
            'delivery_photo' => $photo2,
            'items' => [
                [
                    'purchase_order_item_id' => $po->items->first()->id,
                    'product_id' => $product->id,
                    'quantity_received' => 4, // Remaining 4
                    'batch_no' => 'BATCH002',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                    'condition' => 'Good',
                ],
            ],
        ])->assertStatus(201)
            ->assertJsonPath('data.status', GoodsReceipt::STATUS_COMPLETED);

        // Verify PO status updated
        $po->refresh();
        $this->assertEquals(PurchaseOrder::STATUS_COMPLETED, $po->status);
    }

    public function test_organization_isolation(): void
    {
        $orgA = Organization::factory()->create();
        $orgB = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->forSupplier($supplier)->create();
        
        $userA = User::factory()->healthcareUser()->forOrganization($orgA)->create();
        $userB = User::factory()->healthcareUser()->forOrganization($orgB)->create();
        
        $poB = PurchaseOrder::factory()
            ->approved()
            ->forOrganization($orgB)
            ->forSupplier($supplier)
            ->createdBy($userB)
            ->create();
            
        $poB->items()->create([
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'subtotal' => 500000,
        ]);
        
        $poB->recalculateTotals();
        $poB->save();

        // User A tries to confirm receipt for Org B's PO
        $this->actingAsUser($userA);

        Storage::fake('public');
        $photo = UploadedFile::fake()->image('delivery.jpg');

        $this->postJson('/api/goods-receipts', [
            'purchase_order_id' => $poB->id,
            'delivery_order_number' => 'DO-001',
            'delivery_photo' => $photo,
            'items' => [
                [
                    'purchase_order_item_id' => $poB->items->first()->id,
                    'product_id' => $product->id,
                    'quantity_received' => 5,
                    'batch_no' => 'BATCH001',
                    'expiry_date' => now()->addYear()->format('Y-m-d'),
                ],
            ],
        ])->assertStatus(403); // Should be forbidden due to Policy
    }

    public function test_concurrent_gr_creation_race_condition(): void
    {
        [$organization, $supplier, $product, $po, $user] = $this->makeApprovedPOWithItems();
        
        Storage::fake('public');
        
        // Simulate concurrent requests by calling service directly
        $service = app(GoodsReceiptService::class);
        
        $photo1 = UploadedFile::fake()->image('delivery1.jpg');
        $photo2 = UploadedFile::fake()->image('delivery2.jpg');
        
        $items = [
            [
                'purchase_order_item_id' => $po->items->first()->id,
                'quantity_received' => 3,
                'batch_no' => 'BATCH001',
                'expiry_date' => now()->addYear()->format('Y-m-d'),
                'condition' => 'Good',
            ],
        ];

        // Both should succeed and create deliveries for the same GR
        $gr1 = $service->addDelivery($po, $user, $items, 'DO-001', $photo1);
        $gr2 = $service->addDelivery($po->fresh(), $user, $items, 'DO-002', $photo2);

        // Should be the same GR with 2 deliveries
        $this->assertEquals($gr1->id, $gr2->id);
        $this->assertEquals(2, $gr1->fresh()->deliveries()->count());
    }
}