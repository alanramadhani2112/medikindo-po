<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class UpdateProductPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Update harga produk yang masih 0 atau belum ada harga
     */
    public function run(): void
    {
        $this->command->info('Updating product prices...');

        // Get ALL products to update cost_price and selling_price
        $allProducts = Product::all();
        
        $this->command->info("Processing {$allProducts->count()} products");

        $updated = 0;

        foreach ($allProducts as $product) {
            // If price is 0 or null, generate new price
            if ($product->price == 0 || $product->price === null) {
                $price = $this->generatePrice($product);
            } else {
                $price = $product->price;
            }
            
            // Always update cost_price and selling_price based on price
            $costPrice = bcmul($price, '0.70', 2); // Cost = 70% of selling price
            $sellingPrice = $price;
            
            $product->update([
                'price' => $price,
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
            ]);

            $this->command->info("✓ Updated: {$product->name}");
            $this->command->info("  Price: Rp " . number_format($price, 0, ',', '.'));
            $this->command->info("  Cost: Rp " . number_format($costPrice, 0, ',', '.') . " | Selling: Rp " . number_format($sellingPrice, 0, ',', '.'));
            $updated++;
        }

        $this->command->info("✓ Total products updated: {$updated}");
        $this->command->info('✓ Price update completed successfully!');
    }

    /**
     * Generate realistic price based on product characteristics
     */
    private function generatePrice(Product $product): string
    {
        $name = strtolower($product->name);
        $sku = strtolower($product->sku);
        
        // Alat kesehatan mahal
        if (str_contains($name, 'tensimeter') || str_contains($name, 'nebulizer')) {
            return (string) rand(200000, 400000);
        }
        
        if (str_contains($name, 'pulse oximeter') || str_contains($name, 'thermometer digital')) {
            return (string) rand(150000, 250000);
        }
        
        // Rapid test
        if (str_contains($name, 'rapid test')) {
            return (string) rand(100000, 200000);
        }
        
        // Insulin
        if (str_contains($name, 'insulin')) {
            return (string) rand(250000, 350000);
        }
        
        // Inhaler
        if (str_contains($name, 'inhaler')) {
            return (string) rand(60000, 180000);
        }
        
        // Tabung lab
        if (str_contains($name, 'tabung') || str_contains($name, 'vacutainer')) {
            return (string) rand(70000, 90000);
        }
        
        // Sarung tangan, masker (box)
        if (str_contains($name, 'sarung tangan') || str_contains($name, 'masker')) {
            return (string) rand(50000, 80000);
        }
        
        // Spuit (box)
        if (str_contains($name, 'spuit') || str_contains($name, 'syringe')) {
            return (string) rand(25000, 40000);
        }
        
        // Cairan infus
        if (str_contains($name, 'infus') || str_contains($name, 'nacl') || 
            str_contains($name, 'ringer') || str_contains($name, 'dextrose') ||
            str_contains($name, 'asering')) {
            return (string) rand(18000, 30000);
        }
        
        // Antiseptik (botol besar)
        if (str_contains($name, 'alkohol') || str_contains($name, 'hand sanitizer') ||
            str_contains($name, 'chlorhexidine')) {
            return (string) rand(30000, 60000);
        }
        
        // Vitamin & suplemen (botol)
        if (str_contains($name, 'vitamin') || str_contains($name, 'multivitamin') ||
            str_contains($name, 'zinc') || str_contains($name, 'calcium')) {
            return (string) rand(40000, 70000);
        }
        
        // Obat sirup anak
        if (str_contains($name, 'syrup') || str_contains($name, 'sirup') ||
            str_contains($name, 'drops') || str_contains($name, 'tetes')) {
            return (string) rand(18000, 35000);
        }
        
        // Obat injeksi
        if (str_contains($name, 'injection') || str_contains($name, 'ampul') ||
            str_contains($name, 'vial') || str_contains($sku, 'inj-')) {
            return (string) rand(12000, 40000);
        }
        
        // Obat mahal (antibiotik kuat, obat jantung)
        if (str_contains($name, 'clopidogrel') || str_contains($name, 'atorvastatin') ||
            str_contains($name, 'levofloxacin') || str_contains($name, 'azithromycin') ||
            str_contains($name, 'cefixime') || str_contains($name, 'montelukast')) {
            return (string) rand(20000, 35000);
        }
        
        // Obat sedang
        if (str_contains($name, 'amoxicillin') || str_contains($name, 'ciprofloxacin') ||
            str_contains($name, 'metformin') || str_contains($name, 'captopril') ||
            str_contains($name, 'amlodipine') || str_contains($name, 'simvastatin')) {
            return (string) rand(10000, 18000);
        }
        
        // Obat murah (generik)
        if (str_contains($name, 'paracetamol') || str_contains($name, 'cetirizine') ||
            str_contains($name, 'ibuprofen') || str_contains($name, 'omeprazole') ||
            str_contains($name, 'loperamide') || str_contains($name, 'domperidone')) {
            return (string) rand(5000, 10000);
        }
        
        // Salep & krim
        if (str_contains($name, 'cream') || str_contains($name, 'ointment') ||
            str_contains($name, 'salep') || str_contains($name, 'krim') ||
            str_contains($name, 'lotion')) {
            return (string) rand(20000, 40000);
        }
        
        // Tetes mata/telinga
        if (str_contains($name, 'eye drops') || str_contains($name, 'ear drops') ||
            str_contains($name, 'tetes mata') || str_contains($name, 'tetes telinga')) {
            return (string) rand(15000, 45000);
        }
        
        // Perban & plester
        if (str_contains($name, 'perban') || str_contains($name, 'plester') ||
            str_contains($name, 'kasa') || str_contains($name, 'verband')) {
            return (string) rand(5000, 15000);
        }
        
        // Default: obat tablet/kapsul biasa
        return (string) rand(8000, 15000);
    }
}
