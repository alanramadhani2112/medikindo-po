<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopulatePOUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Populate unit_id untuk purchase_order_items yang masih NULL.
     * Set ke base_unit_id dari product.
     */
    public function run(): void
    {
        $this->command->info('Populating unit_id for purchase order items...');
        
        $items = DB::table('purchase_order_items')
            ->whereNull('unit_id')
            ->get();
        
        $updated = 0;
        $errors = 0;
        
        foreach ($items as $item) {
            $product = Product::find($item->product_id);
            
            if ($product && $product->base_unit_id) {
                DB::table('purchase_order_items')
                    ->where('id', $item->id)
                    ->update(['unit_id' => $product->base_unit_id]);
                $updated++;
            } else {
                $this->command->error("  Product not found or missing base_unit_id for PO item {$item->id}");
                $errors++;
            }
        }
        
        $this->command->info("✓ PO item units populated:");
        $this->command->info("  - Updated: {$updated} items");
        $this->command->info("  - Errors: {$errors} items");
    }
}
