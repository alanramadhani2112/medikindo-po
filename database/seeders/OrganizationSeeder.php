<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating organizations...');

        $organizations = [
            [
                'name'                       => 'RS Umum Medika Utama',
                'code'                       => 'RSU-MU',
                'type'                       => 'hospital',
                'address'                    => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone'                      => '021-5551234',
                'email'                      => 'info@rsumedikautama.com',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 5.00,
            ],
            [
                'name'                       => 'Klinik Sehat Sentosa',
                'code'                       => 'KLN-SS',
                'type'                       => 'clinic',
                'address'                    => 'Jl. Gatot Subroto No. 45, Jakarta Selatan',
                'phone'                      => '021-7778888',
                'email'                      => 'admin@kliniksehatsentosa.com',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 3.00,
            ],
            [
                'name'                       => 'RS Harapan Bunda',
                'code'                       => 'RSU-HB',
                'type'                       => 'hospital',
                'address'                    => 'Jl. Thamrin No. 88, Jakarta Pusat',
                'phone'                      => '021-3334567',
                'email'                      => 'contact@rsharapanbunda.com',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 7.00,
            ],
            [
                'name'                       => 'Klinik Pratama Husada',
                'code'                       => 'KLN-PH',
                'type'                       => 'clinic',
                'address'                    => 'Jl. Kuningan Raya No. 12, Jakarta Selatan',
                'phone'                      => '021-5559999',
                'email'                      => 'info@klinikpratamahusada.com',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 2.00,
            ],
            [
                'name'                       => 'RS Ibu dan Anak Permata',
                'code'                       => 'RSIA-PM',
                'type'                       => 'hospital',
                'address'                    => 'Jl. Rasuna Said No. 56, Jakarta Selatan',
                'phone'                      => '021-8887777',
                'email'                      => 'admin@rsiapermata.com',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 5.00,
            ],
            [
                'name'                       => 'Puskesmas Cempaka Putih',
                'code'                       => 'PKM-CP',
                'type'                       => 'puskesmas',
                'address'                    => 'Jl. Cempaka Putih Tengah No. 1, Jakarta Pusat',
                'phone'                      => '021-4445566',
                'email'                      => 'puskesmas.cempakaputih@jakarta.go.id',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 0.00,
            ],
            [
                'name'                       => 'Klinik Spesialis Jantung Sehat',
                'code'                       => 'KLN-JS',
                'type'                       => 'clinic',
                'address'                    => 'Jl. Menteng Raya No. 34, Jakarta Pusat',
                'phone'                      => '021-3339988',
                'email'                      => 'info@klinikjantungsehat.com',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 4.00,
            ],
            [
                'name'                       => 'RS Ortopedi Prima',
                'code'                       => 'RSO-PR',
                'type'                       => 'hospital',
                'address'                    => 'Jl. Fatmawati No. 99, Jakarta Selatan',
                'phone'                      => '021-7776655',
                'email'                      => 'contact@rsortopediprima.com',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 6.00,
            ],
        ];

        $created = 0;
        foreach ($organizations as $orgData) {
            $exists = Organization::where('code', $orgData['code'])->exists();

            if (!$exists) {
                Organization::create($orgData);
                $created++;
                $this->command->info("✓ Created: {$orgData['name']}");
            } else {
                $this->command->warn("⊘ Skipped (exists): {$orgData['name']}");
            }
        }

        $this->command->info("✓ Total organizations created: {$created}");
    }
}
