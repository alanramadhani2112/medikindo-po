<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class UpdateProductCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Update kategori produk yang masih kosong
     */
    public function run(): void
    {
        $this->command->info('Updating product categories...');

        // Get products without category
        $products = Product::whereNull('category')->orWhere('category', '')->get();
        
        $this->command->info("Processing {$products->count()} products");

        $updated = 0;

        foreach ($products as $product) {
            $category = $this->determineCategory($product);
            
            $product->update([
                'category' => $category,
            ]);

            $this->command->info("✓ Updated: {$product->name} → {$category}");
            $updated++;
        }

        $this->command->info("✓ Total products updated: {$updated}");
        $this->command->info('✓ Category update completed successfully!');
        
        // Show summary
        $this->showCategorySummary();
    }

    /**
     * Determine category based on product name and SKU
     */
    private function determineCategory(Product $product): string
    {
        $name = strtolower($product->name);
        $sku = strtolower($product->sku);
        
        // Kardiovaskular (Obat Jantung & Pembuluh Darah)
        if (str_contains($sku, 'card-') || 
            str_contains($name, 'bisoprolol') || 
            str_contains($name, 'clopidogrel') ||
            str_contains($name, 'atorvastatin') || 
            str_contains($name, 'valsartan') ||
            str_contains($name, 'isosorbide') || 
            str_contains($name, 'spironolactone') ||
            str_contains($name, 'digoxin') || 
            str_contains($name, 'nitroglycerin') ||
            str_contains($name, 'amlodipine') || 
            str_contains($name, 'captopril') ||
            str_contains($name, 'simvastatin')) {
            return 'Kardiovaskular';
        }
        
        // Diabetes (Obat Diabetes & Insulin)
        if (str_contains($sku, 'diab-') || 
            str_contains($name, 'glimepiride') || 
            str_contains($name, 'glibenclamide') ||
            str_contains($name, 'acarbose') || 
            str_contains($name, 'insulin') ||
            str_contains($name, 'metformin') ||
            str_contains($name, 'test strip gula')) {
            return 'Diabetes';
        }
        
        // Pernapasan (Obat Asma, Batuk, Pilek)
        if (str_contains($sku, 'resp-') || 
            str_contains($name, 'salbutamol') || 
            str_contains($name, 'budesonide') ||
            str_contains($name, 'inhaler') || 
            str_contains($name, 'ambroxol') ||
            str_contains($name, 'loratadine') || 
            str_contains($name, 'dextromethorphan') ||
            str_contains($name, 'guaifenesin') || 
            str_contains($name, 'pseudoephedrine') ||
            str_contains($name, 'montelukast') ||
            str_contains($name, 'cetirizine')) {
            return 'Pernapasan';
        }
        
        // Pencernaan (Obat Lambung, Diare, Sembelit)
        if (str_contains($sku, 'gast-') || 
            str_contains($name, 'omeprazole') ||
            str_contains($name, 'sucralfate') || 
            str_contains($name, 'bismuth') ||
            str_contains($name, 'metoclopramide') || 
            str_contains($name, 'domperidone') ||
            str_contains($name, 'lactulose') || 
            str_contains($name, 'attapulgite') ||
            str_contains($name, 'loperamide') ||
            str_contains($name, 'simethicone') || 
            str_contains($name, 'pancreatin') ||
            str_contains($name, 'probiotik')) {
            return 'Pencernaan';
        }
        
        // Antibiotik
        if (str_contains($sku, 'anti-') || 
            str_contains($name, 'amoxicillin') ||
            str_contains($name, 'ciprofloxacin') || 
            str_contains($name, 'azithromycin') ||
            str_contains($name, 'cefixime') || 
            str_contains($name, 'levofloxacin') ||
            str_contains($name, 'metronidazole') || 
            str_contains($name, 'doxycycline') ||
            str_contains($name, 'clindamycin') || 
            str_contains($name, 'cotrimoxazole') ||
            str_contains($name, 'ceftriaxone')) {
            return 'Antibiotik';
        }
        
        // Mata & Telinga
        if (str_contains($sku, 'opht-') || 
            str_contains($sku, 'otic-') ||
            str_contains($name, 'eye drops') || 
            str_contains($name, 'ear drops') ||
            str_contains($name, 'tetes mata') || 
            str_contains($name, 'tetes telinga') ||
            str_contains($name, 'chloramphenicol eye') || 
            str_contains($name, 'timolol') ||
            str_contains($name, 'artificial tears') || 
            str_contains($name, 'ofloxacin ear') ||
            str_contains($name, 'ciprofloxacin eye') || 
            str_contains($name, 'dexamethasone eye')) {
            return 'Mata & Telinga';
        }
        
        // Endokrin (Hormon & Metabolisme)
        if (str_contains($sku, 'endo-') || 
            str_contains($name, 'levothyroxine') ||
            str_contains($name, 'methylprednisolone') || 
            str_contains($name, 'prednisone') ||
            str_contains($name, 'calcium') || 
            str_contains($name, 'vitamin d')) {
            return 'Endokrin';
        }
        
        // Neurologi & Psikiatri
        if (str_contains($sku, 'neuro-') || 
            str_contains($sku, 'psych-') ||
            str_contains($name, 'diazepam') || 
            str_contains($name, 'phenytoin') ||
            str_contains($name, 'carbamazepine') || 
            str_contains($name, 'fluoxetine') ||
            str_contains($name, 'alprazolam') || 
            str_contains($name, 'haloperidol')) {
            return 'Neurologi & Psikiatri';
        }
        
        // Laboratorium (Alat Lab & Rapid Test)
        if (str_contains($sku, 'lab-') || 
            str_contains($name, 'tabung') ||
            str_contains($name, 'vacutainer') || 
            str_contains($name, 'urine container') ||
            str_contains($name, 'rapid test') || 
            str_contains($name, 'lancet')) {
            return 'Laboratorium';
        }
        
        // Alat Kesehatan
        if (str_contains($name, 'tensimeter') || 
            str_contains($name, 'nebulizer') ||
            str_contains($name, 'pulse oximeter') || 
            str_contains($name, 'thermometer') ||
            str_contains($name, 'sarung tangan') || 
            str_contains($name, 'masker') ||
            str_contains($name, 'spuit') || 
            str_contains($name, 'syringe')) {
            return 'Alat Kesehatan';
        }
        
        // Cairan Infus
        if (str_contains($name, 'infus') || 
            str_contains($name, 'nacl') ||
            str_contains($name, 'ringer') || 
            str_contains($name, 'dextrose') ||
            str_contains($name, 'asering')) {
            return 'Cairan Infus';
        }
        
        // Antiseptik & Desinfektan
        if (str_contains($name, 'alkohol') || 
            str_contains($name, 'hand sanitizer') ||
            str_contains($name, 'chlorhexidine') || 
            str_contains($name, 'povidone') ||
            str_contains($name, 'betadine')) {
            return 'Antiseptik';
        }
        
        // Vitamin & Suplemen
        if (str_contains($name, 'vitamin') || 
            str_contains($name, 'multivitamin') ||
            str_contains($name, 'zinc') || 
            str_contains($name, 'suplemen')) {
            return 'Vitamin & Suplemen';
        }
        
        // Analgesik & Antipiretik (Obat Nyeri & Demam)
        if (str_contains($name, 'paracetamol') || 
            str_contains($name, 'ibuprofen') ||
            str_contains($name, 'aspirin') || 
            str_contains($name, 'asam mefenamat') ||
            str_contains($name, 'ketorolac')) {
            return 'Analgesik & Antipiretik';
        }
        
        // Dermatologi (Obat Kulit)
        if (str_contains($name, 'cream') || 
            str_contains($name, 'ointment') ||
            str_contains($name, 'salep') || 
            str_contains($name, 'krim') ||
            str_contains($name, 'lotion') || 
            str_contains($name, 'gel')) {
            return 'Dermatologi';
        }
        
        // Perban & Wound Care
        if (str_contains($name, 'perban') || 
            str_contains($name, 'plester') ||
            str_contains($name, 'kasa') || 
            str_contains($name, 'verband')) {
            return 'Perban & Wound Care';
        }
        
        // Default: Obat Umum
        return 'Obat Umum';
    }

    /**
     * Show category summary
     */
    private function showCategorySummary(): void
    {
        $this->command->info("\n" . str_repeat("=", 80));
        $this->command->info("CATEGORY SUMMARY:");
        $this->command->info(str_repeat("-", 80));
        
        $categories = Product::selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get();
        
        foreach ($categories as $cat) {
            $this->command->info(sprintf("%-30s: %d products", $cat->category, $cat->total));
        }
        
        $this->command->info(str_repeat("=", 80));
    }
}
