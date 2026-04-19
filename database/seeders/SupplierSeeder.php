<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating suppliers...');

        $suppliers = [
            [
                'name'       => 'PT Kimia Farma Trading & Distribution',
                'code'       => 'KFTD',
                'address'    => 'Jl. Veteran No. 9, Jakarta Pusat 10110',
                'phone'      => '021-3841808',
                'email'      => 'trading@kimiafarma.co.id',
                'npwp'       => '01.234.567.8-901.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Kalbe Farma Tbk',
                'code'       => 'KLBF',
                'address'    => 'Jl. Let. Jend. Suprapto Kav. 4, Jakarta 10510',
                'phone'      => '021-4212808',
                'email'      => 'corporate@kalbe.co.id',
                'npwp'       => '01.345.678.9-012.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Sanbe Farma',
                'code'       => 'SNBF',
                'address'    => 'Jl. Raya Cimahi No. 1, Bandung 40525',
                'phone'      => '022-6654321',
                'email'      => 'info@sanbe.co.id',
                'npwp'       => '01.456.789.0-123.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Indofarma Global Medika',
                'code'       => 'INAF',
                'address'    => 'Jl. Indofarma No. 1, Bekasi 17530',
                'phone'      => '021-8984555',
                'email'      => 'marketing@indofarma.id',
                'npwp'       => '01.567.890.1-234.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Tempo Scan Pacific Tbk',
                'code'       => 'TSPC',
                'address'    => 'Jl. Industri Raya Blok C3 No. 7-9, Jakarta 13930',
                'phone'      => '021-4600808',
                'email'      => 'corporate@thetempogroup.com',
                'npwp'       => '01.678.901.2-345.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Dexa Medica',
                'code'       => 'DXMD',
                'address'    => 'Jl. Bambang Utoyo No. 138, Palembang 30137',
                'phone'      => '0711-710710',
                'email'      => 'info@dexa-medica.com',
                'npwp'       => '01.789.012.3-456.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Pharos Indonesia',
                'code'       => 'PHAR',
                'address'    => 'Jl. Garuda No. 68, Jakarta 12950',
                'phone'      => '021-8300288',
                'email'      => 'marketing@pharos.co.id',
                'npwp'       => '01.890.123.4-567.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Merck Indonesia',
                'code'       => 'MERK',
                'address'    => 'Jl. TB Simatupang Kav. 88, Jakarta 12520',
                'phone'      => '021-78838080',
                'email'      => 'info.indonesia@merckgroup.com',
                'npwp'       => '01.901.234.5-678.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Novartis Indonesia',
                'code'       => 'NOVA',
                'address'    => 'Jl. Jend. Sudirman Kav. 76-78, Jakarta 12910',
                'phone'      => '021-2991888',
                'email'      => 'indonesia.info@novartis.com',
                'npwp'       => '01.012.345.6-789.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Sanofi-Aventis Indonesia',
                'code'       => 'SNFI',
                'address'    => 'Jl. MH Thamrin Kav. 57, Jakarta 10350',
                'phone'      => '021-3192-7000',
                'email'      => 'indonesia@sanofi.com',
                'npwp'       => '01.123.456.7-890.000',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Bayer Indonesia',
                'code'       => 'BAYR',
                'address'    => 'Jl. Jend. Gatot Subroto Kav. 42, Jakarta 12710',
                'phone'      => '021-5261000',
                'email'      => 'info.indonesia@bayer.com',
                'npwp'       => '01.234.567.8-901.001',
                'is_active'  => true,
            ],
            [
                'name'       => 'PT Pfizer Indonesia',
                'code'       => 'PFIZ',
                'address'    => 'World Trade Center 6, Jakarta 12920',
                'phone'      => '021-29927000',
                'email'      => 'indonesia@pfizer.com',
                'npwp'       => '01.345.678.9-012.001',
                'is_active'  => true,
            ],
        ];

        $created = 0;
        foreach ($suppliers as $supplierData) {
            $exists = Supplier::where('code', $supplierData['code'])->exists();

            if (!$exists) {
                Supplier::create($supplierData);
                $created++;
                $this->command->info("✓ Created: {$supplierData['name']}");
            } else {
                $this->command->warn("⊘ Skipped (exists): {$supplierData['name']}");
            }
        }

        $this->command->info("✓ Total suppliers created: {$created}");
    }
}
