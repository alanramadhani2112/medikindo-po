<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seed master units untuk sistem multi-unit.
     * Total: 16 units (base, packaging, volume, weight, bundle)
     */
    public function run(): void
    {
        $this->command->info('Seeding units master data...');

        $units = [
            // Base Units - satuan terkecil
            [
                'name' => 'Pcs',
                'symbol' => 'pcs',
                'type' => 'base',
                'description' => 'Piece - satuan terkecil untuk barang countable',
            ],
            [
                'name' => 'Tablet',
                'symbol' => 'tab',
                'type' => 'base',
                'description' => 'Tablet obat',
            ],
            [
                'name' => 'Kapsul',
                'symbol' => 'kap',
                'type' => 'base',
                'description' => 'Kapsul obat',
            ],
            [
                'name' => 'Ampul',
                'symbol' => 'amp',
                'type' => 'base',
                'description' => 'Ampul injeksi',
            ],
            [
                'name' => 'Vial',
                'symbol' => 'vial',
                'type' => 'base',
                'description' => 'Vial injeksi',
            ],
            
            // Packaging Units - kemasan
            [
                'name' => 'Box',
                'symbol' => 'box',
                'type' => 'packaging',
                'description' => 'Box/kotak kemasan',
            ],
            [
                'name' => 'Strip',
                'symbol' => 'strip',
                'type' => 'packaging',
                'description' => 'Strip blister untuk tablet/kapsul',
            ],
            [
                'name' => 'Botol',
                'symbol' => 'btl',
                'type' => 'packaging',
                'description' => 'Botol untuk cairan/sirup',
            ],
            [
                'name' => 'Pack',
                'symbol' => 'pack',
                'type' => 'packaging',
                'description' => 'Pack/paket',
            ],
            [
                'name' => 'Karton',
                'symbol' => 'ctn',
                'type' => 'packaging',
                'description' => 'Karton besar untuk bulk packaging',
            ],
            
            // Volume Units
            [
                'name' => 'Liter',
                'symbol' => 'L',
                'type' => 'volume',
                'description' => 'Liter untuk cairan',
            ],
            [
                'name' => 'Mililiter',
                'symbol' => 'mL',
                'type' => 'volume',
                'description' => 'Mililiter untuk cairan',
            ],
            
            // Weight Units
            [
                'name' => 'Gram',
                'symbol' => 'g',
                'type' => 'weight',
                'description' => 'Gram untuk berat',
            ],
            [
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'type' => 'weight',
                'description' => 'Kilogram untuk berat',
            ],
            
            // Bundle Units
            [
                'name' => 'Set',
                'symbol' => 'set',
                'type' => 'bundle',
                'description' => 'Set lengkap (bundle of items)',
            ],
            [
                'name' => 'Roll',
                'symbol' => 'roll',
                'type' => 'bundle',
                'description' => 'Roll/gulungan (untuk perban, plester)',
            ],
        ];

        foreach ($units as $unit) {
            DB::table('units')->insert([
                'name' => $unit['name'],
                'symbol' => $unit['symbol'],
                'type' => $unit['type'],
                'description' => $unit['description'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✓ Units seeded successfully: ' . count($units) . ' units');
        $this->command->info('  - Base units: 5');
        $this->command->info('  - Packaging units: 5');
        $this->command->info('  - Volume units: 2');
        $this->command->info('  - Weight units: 2');
        $this->command->info('  - Bundle units: 2');
    }
}
