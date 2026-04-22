<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Product;
use App\Models\Supplier;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function test_anyone_authenticated_can_list_products(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        Product::factory()->count(5)->create();

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_product_list_can_filter_by_narcotic(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAsHealthcareUser($organization);
        Product::factory()->narcotic()->count(2)->create();
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/products?narcotic=1');
        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_healthcare_user_can_create_product(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $this->actingAsHealthcareUser($organization);

        $this->postJson('/api/products', [
            'supplier_id'  => $supplier->id,
            'name'         => 'Amoxicillin 500mg',
            'sku'          => 'AMX-500',
            'unit'         => 'Tablet',
            'price'        => 3000,
            'cost_price'   => 2500,
            'selling_price'=> 3000,
        ])->assertStatus(201)
            ->assertJsonPath('product.name', 'Amoxicillin 500mg');
    }

    public function test_approver_cannot_create_product(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $this->actingAsApprover($organization);

        $this->postJson('/api/products', [
            'supplier_id' => $supplier->id,
            'name'        => 'Test Drug',
            'sku'         => 'TD-001',
            'unit'        => 'Box',
            'price'       => 10000,
        ])->assertStatus(403);
    }

    public function test_sku_must_be_unique(): void
    {
        $organization = Organization::factory()->create();
        $supplier = Supplier::factory()->create();
        $this->actingAsHealthcareUser($organization);
        Product::factory()->forSupplier($supplier)->create(['sku' => 'DUP-SKU']);

        $this->postJson('/api/products', [
            'supplier_id' => $supplier->id,
            'name'        => 'Another Drug',
            'sku'         => 'DUP-SKU',
            'unit'        => 'Box',
            'price'       => 5000,
        ])->assertStatus(422)->assertJsonValidationErrors('sku');
    }

    public function test_destroy_deactivates_product(): void
    {
        $organization = Organization::factory()->create();
        $product = Product::factory()->create();
        $this->actingAsHealthcareUser($organization);

        $this->deleteJson("/api/products/{$product->id}")->assertOk();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
    }
}
