<?php

namespace Database\Seeders;

use App\Models\TaxConfiguration;
use Illuminate\Database\Seeder;

class TaxConfigurationSeeder extends Seeder
{
    /**
     * Seed the tax_configurations table with default PPN rates and e-Meterai threshold.
     * 
     * Records:
     * - PPN Standard 11% (default, effective 2022-04-01 per PMK 65/2022)
     * - PPN 12% (effective 2025-01-01 per UU HPP)
     * - EMeterai_Threshold: Rp 5.000.000 (effective 2021-10-01 per PP 86/2021)
     */
    public function run(): void
    {
        $configs = [
            [
                'name'           => 'PPN Standard',
                'rate'           => 11.00,
                'is_default'     => true,
                'effective_date' => '2022-04-01',
                'description'    => 'PPN 11% berlaku sejak 1 April 2022 berdasarkan UU HPP No. 7/2021',
            ],
            [
                'name'           => 'PPN 12%',
                'rate'           => 12.00,
                'is_default'     => false,
                'effective_date' => '2025-01-01',
                'description'    => 'PPN 12% berlaku sejak 1 Januari 2025 berdasarkan UU HPP No. 7/2021',
            ],
            [
                'name'           => 'EMeterai_Threshold',
                'rate'           => 5000000.00,
                'is_default'     => false,
                'effective_date' => '2021-10-01',
                'description'    => 'Threshold nilai dokumen yang wajib e-Meterai (Rp 5.000.000) berdasarkan PP 86/2021',
            ],
        ];

        foreach ($configs as $config) {
            TaxConfiguration::firstOrCreate(
                ['name' => $config['name']],
                $config
            );
        }
    }
}
