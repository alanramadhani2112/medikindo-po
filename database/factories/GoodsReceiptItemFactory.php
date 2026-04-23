<?php

namespace Database\Factories;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoodsReceiptItemFactory extends Factory
{
    protected $model = GoodsReceiptItem::class;

    public function definition(): array
    {
        return [
            'goods_receipt_id'      => GoodsReceipt::factory(),
            'purchase_order_item_id'=> PurchaseOrderItem::factory(),
            'product_id'            => Product::factory(),
            'quantity_ordered'      => $this->faker->randomFloat(3, 1, 100),
            'quantity_received'     => $this->faker->randomFloat(3, 1, 100),
            'unit_price'            => $this->faker->randomFloat(2, 1000, 50000),
            'batch_number'          => $this->faker->optional()->regexify('[A-Z]{2}[0-9]{6}'),
            'expiry_date'           => $this->faker->optional()->dateTimeBetween('+6 months', '+2 years'),
            'notes'                 => $this->faker->optional()->sentence(),
        ];
    }

    public function forGoodsReceipt(GoodsReceipt $goodsReceipt): static
    {
        return $this->state(fn(array $attributes) => [
            'goods_receipt_id' => $goodsReceipt->id,
        ]);
    }

    public function forPurchaseOrderItem(PurchaseOrderItem $poItem): static
    {
        return $this->state(fn(array $attributes) => [
            'purchase_order_item_id' => $poItem->id,
            'product_id' => $poItem->product_id,
        ]);
    }
}