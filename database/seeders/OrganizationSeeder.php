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
                'customer_code'              => 'CUST-RSU-MU',
                'type'                       => 'hospital',
                'address'                    => 'Jl. Sudirman No. 123, Jakarta Pusat',
                'phone'                      => '021-5551234',
                'email'                      => 'info@rsumedikautama.com',
                'npwp'                       => '01.234.567.8-901.000',
                'nik'                        => '3171012345670001',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 5.00,
                'bank_accounts'              => [
                    ['bank' => 'BCA', 'account_number' => '1234567890', 'account_name' => 'RS Umum Medika Utama']
                ],
            ],
            [
                'name'                       => 'Klinik Sehat Sentosa',
                'code'                       => 'KLN-SS',
                'customer_code'              => 'CUST-KLN-SS',
                'type'                       => 'clinic',
                'address'                    => 'Jl. Gatot Subroto No. 45, Jakarta Selatan',
                'phone'                      => '021-7778888',
                'email'                      => 'admin@kliniksehatsentosa.com',
                'npwp'                       => '01.234.567.8-901.001',
                'nik'                        => '3171012345670002',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 3.00,
                'bank_accounts'              => [
                    ['bank' => 'Mandiri', 'account_number' => '0987654321', 'account_name' => 'Klinik Sehat Sentosa']
                ],
            ],
            [
                'name'                       => 'RS Harapan Bunda',
                'code'                       => 'RSU-HB',
                'customer_code'              => 'CUST-RSU-HB',
                'type'                       => 'hospital',
                'address'                    => 'Jl. Thamrin No. 88, Jakarta Pusat',
                'phone'                      => '021-3334567',
                'email'                      => 'contact@rsharapanbunda.com',
                'npwp'                       => '01.234.567.8-901.002',
                'nik'                        => '3171012345670003',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 7.00,
                'bank_accounts'              => [
                    ['bank' => 'BNI', 'account_number' => '1122334455', 'account_name' => 'RS Harapan Bunda']
                ],
            ],
            [
                'name'                       => 'Klinik Pratama Husada',
                'code'                       => 'KLN-PH',
                'customer_code'              => 'CUST-KLN-PH',
                'type'                       => 'clinic',
                'address'                    => 'Jl. Kuningan Raya No. 12, Jakarta Selatan',
                'phone'                      => '021-5559999',
                'email'                      => 'info@klinikpratamahusada.com',
                'npwp'                       => '01.234.567.8-901.003',
                'nik'                        => '3171012345670004',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 2.00,
                'bank_accounts'              => [
                    ['bank' => 'BRI', 'account_number' => '5544332211', 'account_name' => 'Klinik Pratama Husada']
                ],
            ],
            [
                'name'                       => 'RS Ibu dan Anak Permata',
                'code'                       => 'RSIA-PM',
                'customer_code'              => 'CUST-RSIA-PM',
                'type'                       => 'hospital',
                'address'                    => 'Jl. Rasuna Said No. 56, Jakarta Selatan',
                'phone'                      => '021-8887777',
                'email'                      => 'admin@rsiapermata.com',
                'npwp'                       => '01.234.567.8-901.004',
                'nik'                        => '3171012345670005',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 5.00,
                'bank_accounts'              => [
                    ['bank' => 'BCA', 'account_number' => '6677889900', 'account_name' => 'RS Ibu dan Anak Permata']
                ],
            ],
            [
                'name'                       => 'Puskesmas Cempaka Putih',
                'code'                       => 'PKM-CP',
                'customer_code'              => 'CUST-PKM-CP',
                'type'                       => 'puskesmas',
                'address'                    => 'Jl. Cempaka Putih Tengah No. 1, Jakarta Pusat',
                'phone'                      => '021-4445566',
                'email'                      => 'puskesmas.cempakaputih@jakarta.go.id',
                'npwp'                       => '01.234.567.8-901.005',
                'nik'                        => '3171012345670006',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 0.00,
                'bank_accounts'              => [
                    ['bank' => 'DKI', 'account_number' => '9988776655', 'account_name' => 'Puskesmas Cempaka Putih']
                ],
            ],
            [
                'name'                       => 'Klinik Spesialis Jantung Sehat',
                'code'                       => 'KLN-JS',
                'customer_code'              => 'CUST-KLN-JS',
                'type'                       => 'clinic',
                'address'                    => 'Jl. Menteng Raya No. 34, Jakarta Pusat',
                'phone'                      => '021-3339988',
                'email'                      => 'info@klinikjantungsehat.com',
                'npwp'                       => '01.234.567.8-901.006',
                'nik'                        => '3171012345670007',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 4.00,
                'bank_accounts'              => [
                    ['bank' => 'Mandiri', 'account_number' => '2233445566', 'account_name' => 'Klinik Spesialis Jantung Sehat']
                ],
            ],
            [
                'name'                       => 'RS Ortopedi Prima',
                'code'                       => 'RSO-PR',
                'customer_code'              => 'CUST-RSO-PR',
                'type'                       => 'hospital',
                'address'                    => 'Jl. Fatmawati No. 99, Jakarta Selatan',
                'phone'                      => '021-7776655',
                'email'                      => 'contact@rsortopediprima.com',
                'npwp'                       => '01.234.567.8-901.007',
                'nik'                        => '3171012345670008',
                'is_active'                  => true,
                'default_tax_rate'           => 11.00,
                'default_discount_percentage'=> 6.00,
                'bank_accounts'              => [
                    ['bank' => 'BCA', 'account_number' => '4455667788', 'account_name' => 'RS Ortopedi Prima']
                ],
            ],
        ];

        $created = 0;
        foreach ($organizations as $orgData) {
            $exists = Organization::where('code', $orgData['code'])->exists();

            if (!$exists) {
                // Encode bank_accounts to JSON
                if (isset($orgData['bank_accounts'])) {
                    $orgData['bank_accounts'] = json_encode($orgData['bank_accounts']);
                }

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
