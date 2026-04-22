<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExtendedProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Menambahkan lebih banyak produk dummy untuk master data
     */
    public function run(): void
    {
        // Get all suppliers
        $suppliers = Supplier::all();

        if ($suppliers->isEmpty()) {
            $this->command->warn('No suppliers found. Please run SupplierSeeder first.');
            return;
        }

        $this->command->info('Creating extended product catalog...');

        // Extended products data - 70+ items
        $productsData = [
            // Obat Jantung & Kardiovaskular (8 items)
            [
                'name' => 'Bisoprolol 5mg',
                'sku' => 'CARD-BISO-5',
                'description' => 'Beta blocker untuk hipertensi dan jantung',
                'price' => 15000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Clopidogrel 75mg',
                'sku' => 'CARD-CLOP-75',
                'description' => 'Antiplatelet untuk pencegahan stroke',
                'price' => 28000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Atorvastatin 20mg',
                'sku' => 'CARD-ATOR-20',
                'description' => 'Statin penurun kolesterol',
                'price' => 18000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Valsartan 80mg',
                'sku' => 'CARD-VALS-80',
                'description' => 'ARB untuk hipertensi',
                'price' => 22000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Isosorbide Dinitrate 5mg',
                'sku' => 'CARD-ISDN-5',
                'description' => 'Vasodilator untuk angina',
                'price' => 12000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Spironolactone 25mg',
                'sku' => 'CARD-SPIRO-25',
                'description' => 'Diuretik hemat kalium',
                'price' => 16000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Digoxin 0.25mg',
                'sku' => 'CARD-DIGO-025',
                'description' => 'Glikosida jantung untuk gagal jantung',
                'price' => 14000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Nitroglycerin Sublingual 0.5mg',
                'sku' => 'CARD-NITRO-05',
                'description' => 'Obat angina akut sublingual',
                'price' => 35000.00,
                'unit' => 'Botol (100 tablet)',
            ],

            // Obat Diabetes (6 items)
            [
                'name' => 'Glimepiride 2mg',
                'sku' => 'DIAB-GLIM-2',
                'description' => 'Sulfonilurea untuk diabetes tipe 2',
                'price' => 13000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Glibenclamide 5mg',
                'sku' => 'DIAB-GLIB-5',
                'description' => 'Obat diabetes oral',
                'price' => 10000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Acarbose 50mg',
                'sku' => 'DIAB-ACAR-50',
                'description' => 'Alpha-glucosidase inhibitor',
                'price' => 20000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Insulin Glargine 100IU/ml',
                'sku' => 'DIAB-INS-GLAR',
                'description' => 'Insulin long-acting',
                'price' => 280000.00,
                'unit' => 'Vial 10ml',
            ],
            [
                'name' => 'Insulin Aspart 100IU/ml',
                'sku' => 'DIAB-INS-ASP',
                'description' => 'Insulin rapid-acting',
                'price' => 320000.00,
                'unit' => 'Vial 10ml',
            ],
            [
                'name' => 'Test Strip Gula Darah',
                'sku' => 'DIAB-STRIP-GLU',
                'description' => 'Strip tes gula darah',
                'price' => 150000.00,
                'unit' => 'Box (50 strips)',
            ],

            // Obat Saluran Pernapasan (8 items)
            [
                'name' => 'Salbutamol Inhaler',
                'sku' => 'RESP-SALB-INH',
                'description' => 'Bronkodilator inhaler untuk asma',
                'price' => 65000.00,
                'unit' => 'Inhaler 200 dosis',
            ],
            [
                'name' => 'Budesonide Inhaler 200mcg',
                'sku' => 'RESP-BUDE-INH',
                'description' => 'Kortikosteroid inhaler',
                'price' => 180000.00,
                'unit' => 'Inhaler 200 dosis',
            ],
            [
                'name' => 'Ambroxol 30mg',
                'sku' => 'RESP-AMBR-30',
                'description' => 'Mukolitik pengencer dahak',
                'price' => 8000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Loratadine 10mg',
                'sku' => 'RESP-LORA-10',
                'description' => 'Antihistamin untuk alergi',
                'price' => 7000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Dextromethorphan Syrup',
                'sku' => 'RESP-DMP-SYR',
                'description' => 'Obat batuk kering',
                'price' => 18000.00,
                'unit' => 'Botol 60ml',
            ],
            [
                'name' => 'Guaifenesin Syrup',
                'sku' => 'RESP-GUAI-SYR',
                'description' => 'Ekspektoran untuk batuk berdahak',
                'price' => 20000.00,
                'unit' => 'Botol 60ml',
            ],
            [
                'name' => 'Pseudoephedrine 60mg',
                'sku' => 'RESP-PSEU-60',
                'description' => 'Dekongestan hidung',
                'price' => 9000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Montelukast 10mg',
                'sku' => 'RESP-MONT-10',
                'description' => 'Obat asma dan alergi',
                'price' => 25000.00,
                'unit' => 'Strip (10 tablet)',
            ],

            // Obat Pencernaan (8 items)
            [
                'name' => 'Sucralfate Syrup',
                'sku' => 'GAST-SUCR-SYR',
                'description' => 'Pelindung mukosa lambung',
                'price' => 28000.00,
                'unit' => 'Botol 100ml',
            ],
            [
                'name' => 'Bismuth Subsalicylate',
                'sku' => 'GAST-BISM-TAB',
                'description' => 'Obat diare dan gangguan pencernaan',
                'price' => 15000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Metoclopramide 10mg',
                'sku' => 'GAST-METO-10',
                'description' => 'Prokinetik anti mual',
                'price' => 6000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Lactulose Syrup',
                'sku' => 'GAST-LACT-SYR',
                'description' => 'Laksatif untuk konstipasi',
                'price' => 45000.00,
                'unit' => 'Botol 200ml',
            ],
            [
                'name' => 'Attapulgite 600mg',
                'sku' => 'GAST-ATTA-600',
                'description' => 'Obat diare',
                'price' => 8000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Simethicone 80mg',
                'sku' => 'GAST-SIME-80',
                'description' => 'Anti kembung',
                'price' => 7000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Pancreatin Tablet',
                'sku' => 'GAST-PANC-TAB',
                'description' => 'Enzim pencernaan',
                'price' => 12000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Probiotik Kapsul',
                'sku' => 'GAST-PROB-CAP',
                'description' => 'Probiotik untuk kesehatan usus',
                'price' => 35000.00,
                'unit' => 'Strip (10 kapsul)',
            ],

            // Antibiotik Tambahan (6 items)
            [
                'name' => 'Cefixime 200mg',
                'sku' => 'ANTI-CEFI-200',
                'description' => 'Antibiotik sefalosporin generasi 3',
                'price' => 30000.00,
                'unit' => 'Strip (10 kapsul)',
            ],
            [
                'name' => 'Levofloxacin 500mg',
                'sku' => 'ANTI-LEVO-500',
                'description' => 'Antibiotik fluorokuinolon',
                'price' => 35000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Metronidazole 500mg',
                'sku' => 'ANTI-METRO-500',
                'description' => 'Antibiotik untuk infeksi anaerob',
                'price' => 12000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Doxycycline 100mg',
                'sku' => 'ANTI-DOXY-100',
                'description' => 'Antibiotik tetrasiklin',
                'price' => 16000.00,
                'unit' => 'Strip (10 kapsul)',
            ],
            [
                'name' => 'Clindamycin 300mg',
                'sku' => 'ANTI-CLIN-300',
                'description' => 'Antibiotik linkomisin',
                'price' => 28000.00,
                'unit' => 'Strip (10 kapsul)',
            ],
            [
                'name' => 'Cotrimoxazole 480mg',
                'sku' => 'ANTI-COTRI-480',
                'description' => 'Antibiotik kombinasi sulfa',
                'price' => 10000.00,
                'unit' => 'Strip (10 tablet)',
            ],

            // Obat Mata & Telinga (6 items)
            [
                'name' => 'Chloramphenicol Eye Drops',
                'sku' => 'EYE-CHLOR-DROP',
                'description' => 'Tetes mata antibiotik',
                'price' => 15000.00,
                'unit' => 'Botol 5ml',
            ],
            [
                'name' => 'Timolol Eye Drops 0.5%',
                'sku' => 'EYE-TIMO-DROP',
                'description' => 'Tetes mata untuk glaukoma',
                'price' => 45000.00,
                'unit' => 'Botol 5ml',
            ],
            [
                'name' => 'Artificial Tears',
                'sku' => 'EYE-ART-TEAR',
                'description' => 'Tetes mata pelumas',
                'price' => 25000.00,
                'unit' => 'Botol 10ml',
            ],
            [
                'name' => 'Ofloxacin Ear Drops',
                'sku' => 'EAR-OFLO-DROP',
                'description' => 'Tetes telinga antibiotik',
                'price' => 28000.00,
                'unit' => 'Botol 5ml',
            ],
            [
                'name' => 'Ciprofloxacin Eye Ointment',
                'sku' => 'EYE-CIPRO-OINT',
                'description' => 'Salep mata antibiotik',
                'price' => 22000.00,
                'unit' => 'Tube 3.5g',
            ],
            [
                'name' => 'Dexamethasone Eye Drops',
                'sku' => 'EYE-DEXA-DROP',
                'description' => 'Tetes mata kortikosteroid',
                'price' => 32000.00,
                'unit' => 'Botol 5ml',
            ],

            // Obat Hormonal & Endokrin (5 items)
            [
                'name' => 'Levothyroxine 100mcg',
                'sku' => 'ENDO-LEVO-100',
                'description' => 'Hormon tiroid',
                'price' => 18000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Methylprednisolone 4mg',
                'sku' => 'ENDO-METH-4',
                'description' => 'Kortikosteroid sistemik',
                'price' => 12000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Prednisone 5mg',
                'sku' => 'ENDO-PRED-5',
                'description' => 'Kortikosteroid oral',
                'price' => 10000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Calcium Carbonate 500mg',
                'sku' => 'ENDO-CALC-500',
                'description' => 'Suplemen kalsium',
                'price' => 15000.00,
                'unit' => 'Botol (30 tablet)',
            ],
            [
                'name' => 'Vitamin D3 + Calcium',
                'sku' => 'ENDO-VD3-CALC',
                'description' => 'Kombinasi vitamin D3 dan kalsium',
                'price' => 55000.00,
                'unit' => 'Botol (30 tablet)',
            ],

            // Obat Neurologi & Psikiatri (6 items)
            [
                'name' => 'Diazepam 5mg',
                'sku' => 'NEURO-DIAZ-5',
                'description' => 'Anxiolytic dan antikonvulsan',
                'price' => 15000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Phenytoin 100mg',
                'sku' => 'NEURO-PHEN-100',
                'description' => 'Antikonvulsan untuk epilepsi',
                'price' => 12000.00,
                'unit' => 'Strip (10 kapsul)',
            ],
            [
                'name' => 'Carbamazepine 200mg',
                'sku' => 'NEURO-CARB-200',
                'description' => 'Antikonvulsan dan mood stabilizer',
                'price' => 18000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Fluoxetine 20mg',
                'sku' => 'PSYCH-FLUO-20',
                'description' => 'Antidepresan SSRI',
                'price' => 22000.00,
                'unit' => 'Strip (10 kapsul)',
            ],
            [
                'name' => 'Alprazolam 0.5mg',
                'sku' => 'PSYCH-ALPRA-05',
                'description' => 'Anxiolytic benzodiazepine',
                'price' => 16000.00,
                'unit' => 'Strip (10 tablet)',
            ],
            [
                'name' => 'Haloperidol 5mg',
                'sku' => 'PSYCH-HALO-5',
                'description' => 'Antipsikotik',
                'price' => 14000.00,
                'unit' => 'Strip (10 tablet)',
            ],

            // Peralatan Laboratorium (7 items)
            [
                'name' => 'Tabung Vacutainer EDTA',
                'sku' => 'LAB-VAC-EDTA',
                'description' => 'Tabung darah EDTA untuk hematologi',
                'price' => 80000.00,
                'unit' => 'Box (100 tubes)',
            ],
            [
                'name' => 'Tabung Vacutainer Plain',
                'sku' => 'LAB-VAC-PLAIN',
                'description' => 'Tabung darah plain untuk serologi',
                'price' => 75000.00,
                'unit' => 'Box (100 tubes)',
            ],
            [
                'name' => 'Urine Container Steril',
                'sku' => 'LAB-URINE-CONT',
                'description' => 'Wadah urine steril',
                'price' => 50000.00,
                'unit' => 'Pack (50 pcs)',
            ],
            [
                'name' => 'Rapid Test HIV',
                'sku' => 'LAB-RAPID-HIV',
                'description' => 'Rapid test kit HIV',
                'price' => 150000.00,
                'unit' => 'Box (25 tests)',
            ],
            [
                'name' => 'Rapid Test Dengue',
                'sku' => 'LAB-RAPID-DENG',
                'description' => 'Rapid test kit dengue NS1/IgG/IgM',
                'price' => 180000.00,
                'unit' => 'Box (25 tests)',
            ],
            [
                'name' => 'Rapid Test Malaria',
                'sku' => 'LAB-RAPID-MAL',
                'description' => 'Rapid test kit malaria',
                'price' => 120000.00,
                'unit' => 'Box (25 tests)',
            ],
            [
                'name' => 'Lancet Steril',
                'sku' => 'LAB-LANCET',
                'description' => 'Lancet untuk pengambilan darah kapiler',
                'price' => 40000.00,
                'unit' => 'Box (200 pcs)',
            ],
        ];

        $totalProducts = 0;
        $skippedProducts = 0;

        // Distribute products to suppliers
        $productsPerSupplier = ceil(count($productsData) / $suppliers->count());

        foreach ($suppliers as $index => $supplier) {
            $startIndex = $index * $productsPerSupplier;
            $productsForSupplier = array_slice($productsData, $startIndex, $productsPerSupplier);

            foreach ($productsForSupplier as $productData) {
                // Create unique SKU for this supplier
                $uniqueSku = $productData['sku'] . '-' . $supplier->code;

                // Check if product already exists
                $exists = Product::where('supplier_id', $supplier->id)
                    ->where('sku', $uniqueSku)
                    ->exists();

                if (!$exists) {
                    Product::create([
                        'supplier_id' => $supplier->id,
                        'name' => $productData['name'],
                        'sku' => $uniqueSku,
                        'description' => $productData['description'],
                        'price' => $productData['price'],
                        'cost_price' => $productData['price'] * 0.8,
                        'selling_price' => $productData['price'],
                        'discount_percentage' => 0.00,
                        'discount_amount' => 0.00,
                        'expiry_date' => Carbon::now()->addYears(rand(1, 3))->format('Y-m-d'),
                        'batch_no' => 'BATCH-' . strtoupper(Str::random(8)),
                        'unit' => $productData['unit'],
                        'is_narcotic' => false,
                        'is_active' => true,
                    ]);
                    $totalProducts++;
                } else {
                    $skippedProducts++;
                }
            }

            $this->command->info("✓ Processed products for supplier: {$supplier->name}");
        }

        $this->command->info("✓ Total new products created: {$totalProducts}");
        $this->command->info("✓ Products skipped (already exist): {$skippedProducts}");
        $this->command->info('✓ Extended product seeding completed successfully!');
    }
}

