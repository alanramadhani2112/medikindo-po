<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NormalizeProductUnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Normalize existing products.unit (VARCHAR) ke struktur baru:
     * - Parse unit string (contoh: "Box (50 pcs)")
     * - Set base_unit_id di products
     * - Create product_units records
     */
    public function run(): void
    {
        $this->command->info('Starting product units normalization...');
        $this->command->info('This will parse existing unit strings and create product_units records.');
        
        $products = Product::all();
        $totalProcessed = 0;
        $totalErrors = 0;
        $errors = [];

        foreach ($products as $product) {
            try {
                $this->normalizeProductUnit($product);
                $totalProcessed++;
                
                if ($totalProcessed % 10 == 0) {
                    $this->command->info("  Processed: {$totalProcessed}/{$products->count()} products...");
                }
            } catch (\Exception $e) {
                $this->command->error("  Error processing product {$product->id} ({$product->name}): {$e->getMessage()}");
                $totalErrors++;
                $errors[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_string' => $product->unit,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $this->command->newLine();
        $this->command->info('✓ Normalization completed:');
        $this->command->info("  - Total products: {$products->count()}");
        $this->command->info("  - Successfully processed: {$totalProcessed}");
        $this->command->info("  - Errors: {$totalErrors}");
        
        if ($totalErrors > 0) {
            $this->command->newLine();
            $this->command->warn('Products with errors:');
            foreach ($errors as $error) {
                $this->command->warn("  - ID {$error['product_id']}: {$error['product_name']} (unit: {$error['unit_string']})");
                $this->command->warn("    Error: {$error['error']}");
            }
        }
    }

    /**
     * Normalize single product unit
     */
    private function normalizeProductUnit(Product $product): void
    {
        $unitString = $product->unit; // Contoh: "Box (50 pcs)", "Strip (10 tablet)", "Pcs"
        
        // Parse unit string
        $parsed = $this->parseUnitString($unitString);
        
        // Get base unit from database
        $baseUnit = DB::table('units')
            ->where('name', $parsed['base_unit_name'])
            ->first();
        
        if (!$baseUnit) {
            throw new \Exception("Base unit not found: {$parsed['base_unit_name']}");
        }
        
        // Update product.base_unit_id
        $product->update(['base_unit_id' => $baseUnit->id]);
        
        // Check if product_units already exists (prevent duplicate on re-run)
        $existingBaseUnit = DB::table('product_units')
            ->where('product_id', $product->id)
            ->where('unit_id', $baseUnit->id)
            ->exists();
        
        if (!$existingBaseUnit) {
            // Create product_units record for base unit
            DB::table('product_units')->insert([
                'product_id' => $product->id,
                'unit_id' => $baseUnit->id,
                'conversion_to_base' => 1.0000,
                'is_base_unit' => true,
                'is_default_purchase' => true,
                'is_default_sales' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        // If packaging unit exists, create record
        if ($parsed['packaging_unit_name'] && $parsed['conversion_ratio'] > 1) {
            $packagingUnit = DB::table('units')
                ->where('name', $parsed['packaging_unit_name'])
                ->first();
            
            if ($packagingUnit) {
                $existingPackagingUnit = DB::table('product_units')
                    ->where('product_id', $product->id)
                    ->where('unit_id', $packagingUnit->id)
                    ->exists();
                
                if (!$existingPackagingUnit) {
                    DB::table('product_units')->insert([
                        'product_id' => $product->id,
                        'unit_id' => $packagingUnit->id,
                        'conversion_to_base' => $parsed['conversion_ratio'],
                        'is_base_unit' => false,
                        'is_default_purchase' => true,
                        'is_default_sales' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Parse unit string ke structured data
     * 
     * Supported patterns:
     * - "Box (50 pcs)" → packaging: Box, base: Pcs, ratio: 50
     * - "Strip (10 tablet)" → packaging: Strip, base: Tablet, ratio: 10
     * - "Botol (30 tablet)" → packaging: Botol, base: Tablet, ratio: 30
     * - "Pcs" → base: Pcs, ratio: 1
     * - "Ampul" → base: Ampul, ratio: 1
     */
    private function parseUnitString(string $unitString): array
    {
        // Trim whitespace
        $unitString = trim($unitString);
        
        // Pattern 1: "Box (50 pcs)" atau "Strip (10 tablet)"
        if (preg_match('/^(\w+)\s*\((\d+)\s*(\w+)\)$/i', $unitString, $matches)) {
            return [
                'packaging_unit_name' => ucfirst(strtolower($matches[1])),
                'conversion_ratio' => (float) $matches[2],
                'base_unit_name' => ucfirst(strtolower($matches[3])),
            ];
        }
        
        // Pattern 2: "Pcs", "Ampul", "Vial", "Set", "Roll" (base unit only)
        return [
            'packaging_unit_name' => null,
            'conversion_ratio' => 1.0,
            'base_unit_name' => ucfirst(strtolower($unitString)),
        ];
    }
}
