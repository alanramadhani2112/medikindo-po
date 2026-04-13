<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all suppliers
        $suppliers = Supplier::all();

        if ($suppliers->isEmpty()) {
            $this->command->warn('No suppliers found. Please run SupplierSeeder first.');
            return;
        }

        $this->command->info('Creating products for each supplier...');

        // Products data - pharmaceutical and medical supplies
        $productsData = [
            // Obat-obatan Umum
            [
                'name' => 'Paracetamol 500mg',
                'sku' => 'MED-PARA-500',
                'description' => 'Obat pereda nyeri dan penurun demam',
                'price' => 5000.00,
                'unit' => 'Strip (10 tablet)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Amoxicillin 500mg',
                'sku' => 'MED-AMOX-500',
                'description' => 'Antibiotik untuk infeksi bakteri',
                'price' => 15000.00,
                'unit' => 'Strip (10 kapsul)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Omeprazole 20mg',
                'sku' => 'MED-OMEP-20',
                'description' => 'Obat untuk mengurangi asam lambung',
                'price' => 8000.00,
                'unit' => 'Strip (10 kapsul)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Cetirizine 10mg',
                'sku' => 'MED-CETI-10',
                'description' => 'Obat anti alergi',
                'price' => 6000.00,
                'unit' => 'Strip (10 tablet)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Metformin 500mg',
                'sku' => 'MED-METF-500',
                'description' => 'Obat diabetes tipe 2',
                'price' => 12000.00,
                'unit' => 'Strip (10 tablet)',
                'is_narcotic' => false,
                'is_active' => true,
            ],

            // Obat Narkotika (Controlled)
            [
                'name' => 'Morphine Sulfate 10mg',
                'sku' => 'NAR-MORP-10',
                'description' => 'Analgesik narkotika untuk nyeri berat',
                'price' => 50000.00,
                'unit' => 'Ampul',
                'is_narcotic' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tramadol 50mg',
                'sku' => 'NAR-TRAM-50',
                'description' => 'Analgesik opioid untuk nyeri sedang-berat',
                'price' => 25000.00,
                'unit' => 'Strip (10 kapsul)',
                'is_narcotic' => true,
                'is_active' => true,
            ],

            // Alat Kesehatan
            [
                'name' => 'Sarung Tangan Latex (M)',
                'sku' => 'MED-GLOVE-M',
                'description' => 'Sarung tangan medis steril ukuran M',
                'price' => 75000.00,
                'unit' => 'Box (100 pcs)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Masker Medis 3 Ply',
                'sku' => 'MED-MASK-3P',
                'description' => 'Masker bedah 3 lapis',
                'price' => 50000.00,
                'unit' => 'Box (50 pcs)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Spuit 3cc',
                'sku' => 'MED-SYR-3CC',
                'description' => 'Syringe disposable 3cc steril',
                'price' => 30000.00,
                'unit' => 'Box (100 pcs)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Infus Set',
                'sku' => 'MED-INF-SET',
                'description' => 'Set infus lengkap dengan jarum',
                'price' => 15000.00,
                'unit' => 'Set',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Kateter Urine No. 16',
                'sku' => 'MED-CATH-16',
                'description' => 'Kateter urine steril ukuran 16',
                'price' => 20000.00,
                'unit' => 'Pcs',
                'is_narcotic' => false,
                'is_active' => true,
            ],

            // Cairan Infus
            [
                'name' => 'NaCl 0.9% 500ml',
                'sku' => 'INF-NACL-500',
                'description' => 'Cairan infus normal saline',
                'price' => 18000.00,
                'unit' => 'Botol',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Ringer Laktat 500ml',
                'sku' => 'INF-RL-500',
                'description' => 'Cairan infus ringer laktat',
                'price' => 20000.00,
                'unit' => 'Botol',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Dextrose 5% 500ml',
                'sku' => 'INF-D5-500',
                'description' => 'Cairan infus dextrose 5%',
                'price' => 22000.00,
                'unit' => 'Botol',
                'is_narcotic' => false,
                'is_active' => true,
            ],

            // Antiseptik & Desinfektan
            [
                'name' => 'Alkohol 70% 1 Liter',
                'sku' => 'ANT-ALC-1L',
                'description' => 'Alkohol medis 70% untuk antiseptik',
                'price' => 35000.00,
                'unit' => 'Botol',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Betadine Solution 60ml',
                'sku' => 'ANT-BET-60',
                'description' => 'Povidone iodine antiseptik',
                'price' => 25000.00,
                'unit' => 'Botol',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Hand Sanitizer 500ml',
                'sku' => 'ANT-HS-500',
                'description' => 'Hand sanitizer gel 70% alcohol',
                'price' => 40000.00,
                'unit' => 'Botol',
                'is_narcotic' => false,
                'is_active' => true,
            ],

            // Perban & Plester
            [
                'name' => 'Kasa Steril 10x10cm',
                'sku' => 'BAN-KASA-10',
                'description' => 'Kasa steril untuk perawatan luka',
                'price' => 5000.00,
                'unit' => 'Pack (10 pcs)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Perban Elastis 4 inch',
                'sku' => 'BAN-ELAS-4',
                'description' => 'Perban elastis lebar 4 inch',
                'price' => 12000.00,
                'unit' => 'Roll',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Plester Micropore 1 inch',
                'sku' => 'BAN-MICRO-1',
                'description' => 'Plester medis micropore',
                'price' => 8000.00,
                'unit' => 'Roll',
                'is_narcotic' => false,
                'is_active' => true,
            ],

            // Vitamin & Suplemen
            [
                'name' => 'Vitamin C 1000mg',
                'sku' => 'VIT-C-1000',
                'description' => 'Suplemen vitamin C',
                'price' => 50000.00,
                'unit' => 'Botol (30 tablet)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Vitamin B Complex',
                'sku' => 'VIT-BCOM',
                'description' => 'Suplemen vitamin B kompleks',
                'price' => 45000.00,
                'unit' => 'Botol (30 tablet)',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Multivitamin',
                'sku' => 'VIT-MULTI',
                'description' => 'Suplemen multivitamin lengkap',
                'price' => 60000.00,
                'unit' => 'Botol (30 tablet)',
                'is_narcotic' => false,
                'is_active' => true,
            ],

            // Obat Injeksi
            [
                'name' => 'Ceftriaxone 1g Injection',
                'sku' => 'INJ-CEFT-1G',
                'description' => 'Antibiotik injeksi ceftriaxone',
                'price' => 35000.00,
                'unit' => 'Vial',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Dexamethasone 5mg Injection',
                'sku' => 'INJ-DEXA-5',
                'description' => 'Kortikosteroid injeksi',
                'price' => 15000.00,
                'unit' => 'Ampul',
                'is_narcotic' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Ranitidine 50mg Injection',
                'sku' => 'INJ-RANI-50',
                'description' => 'Obat maag injeksi',
                'price' => 12000.00,
                'unit' => 'Ampul',
                'is_narcotic' => false,
                'is_active' => true,
            ],
        ];

        $totalProducts = 0;

        // Assign products to each supplier (distribute evenly)
        foreach ($suppliers as $index => $supplier) {
            // Each supplier gets a subset of products
            $startIndex = ($index * 8) % count($productsData);
            $productsForSupplier = array_slice($productsData, $startIndex, 10);
            
            // If not enough products, wrap around
            if (count($productsForSupplier) < 10) {
                $remaining = 10 - count($productsForSupplier);
                $productsForSupplier = array_merge(
                    $productsForSupplier,
                    array_slice($productsData, 0, $remaining)
                );
            }

            foreach ($productsForSupplier as $productData) {
                // Check if product already exists for this supplier
                $exists = Product::where('supplier_id', $supplier->id)
                    ->where('sku', $productData['sku'])
                    ->exists();

                if (!$exists) {
                    Product::create([
                        'supplier_id' => $supplier->id,
                        'name' => $productData['name'],
                        'sku' => $productData['sku'] . '-' . $supplier->code,
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'unit' => $productData['unit'],
                        'is_narcotic' => $productData['is_narcotic'],
                        'is_active' => $productData['is_active'],
                    ]);
                    $totalProducts++;
                }
            }

            $this->command->info("✓ Created products for supplier: {$supplier->name}");
        }

        $this->command->info("✓ Total products created: {$totalProducts}");
        $this->command->info('✓ Product seeding completed successfully!');
    }
}
