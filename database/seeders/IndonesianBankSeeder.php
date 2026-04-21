<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Daftar bank Indonesia berdasarkan:
 * - Jaringan Prima (PT Rintis Sejahtera) — jaringanprima.co.id/en/bank-code
 * - OJK (Otoritas Jasa Keuangan)
 *
 * Data ini bersifat referensi untuk dropdown pemilihan bank.
 * Tidak membuat rekening bank — hanya master data nama & kode bank.
 */
class IndonesianBankSeeder extends Seeder
{
    /**
     * Daftar bank Indonesia lengkap (nama tampilan + kode 3 digit ATM Bersama/Prima).
     * Diurutkan: Bank BUMN → Bank Swasta Nasional → Bank Daerah → Bank Syariah → Bank Asing/Digital
     */
    public static array $BANKS = [
        // ── Bank BUMN ──────────────────────────────────────────────────────
        ['name' => 'Bank BRI (Bank Rakyat Indonesia)',          'code' => '002'],
        ['name' => 'Bank Mandiri',                              'code' => '008'],
        ['name' => 'Bank BNI (Bank Negara Indonesia)',          'code' => '009'],
        ['name' => 'Bank BTN (Bank Tabungan Negara)',           'code' => '200'],

        // ── Bank Swasta Nasional Besar ─────────────────────────────────────
        ['name' => 'Bank BCA (Bank Central Asia)',              'code' => '014'],
        ['name' => 'Bank CIMB Niaga',                          'code' => '022'],
        ['name' => 'Bank Danamon Indonesia',                    'code' => '011'],
        ['name' => 'Bank Permata',                              'code' => '013'],
        ['name' => 'Bank Maybank Indonesia',                    'code' => '016'],
        ['name' => 'Bank OCBC NISP',                           'code' => '028'],
        ['name' => 'Bank Panin',                                'code' => '019'],
        ['name' => 'Bank UOB Indonesia',                        'code' => '023'],
        ['name' => 'Bank KB Bukopin',                          'code' => '441'],
        ['name' => 'Bank Mega',                                 'code' => '426'],
        ['name' => 'Bank Sinarmas',                             'code' => '153'],
        ['name' => 'Bank Mayapada',                             'code' => '097'],
        ['name' => 'Bank Artha Graha Internasional',            'code' => '037'],
        ['name' => 'Bank Bumi Arta',                            'code' => '076'],
        ['name' => 'Bank Capital Indonesia',                    'code' => '054'],
        ['name' => 'Bank CCB Indonesia',                        'code' => '036'],
        ['name' => 'Bank Ina Perdana',                          'code' => '513'],
        ['name' => 'Bank Index Selindo',                        'code' => '555'],
        ['name' => 'Bank Maspion',                              'code' => '157'],
        ['name' => 'Bank Mestika',                              'code' => '151'],
        ['name' => 'Bank MNC',                                  'code' => '485'],
        ['name' => 'Bank Multiarta Sentosa',                    'code' => '548'],
        ['name' => 'Bank National Nobu',                        'code' => '503'],
        ['name' => 'Bank QNB Indonesia',                        'code' => '167'],
        ['name' => 'Bank Sahabat Sampoerna',                    'code' => '523'],
        ['name' => 'Bank Victoria',                             'code' => '566'],
        ['name' => 'Bank Woori Saudara',                        'code' => '212'],
        ['name' => 'Bank Jtrust Indonesia',                     'code' => '095'],
        ['name' => 'Bank SBI Indonesia',                        'code' => '498'],
        ['name' => 'Bank KEB Hana Indonesia',                   'code' => '484'],
        ['name' => 'Bank Mandiri Taspen',                       'code' => '564'],
        ['name' => 'Bank SMBC Indonesia (BTPN)',                'code' => '213'],

        // ── Bank Pembangunan Daerah (BPD) ──────────────────────────────────
        ['name' => 'Bank BJB (Jawa Barat & Banten)',            'code' => '110'],
        ['name' => 'Bank DKI Jakarta',                          'code' => '111'],
        ['name' => 'Bank BPD DIY (Daerah Istimewa Yogyakarta)', 'code' => '112'],
        ['name' => 'Bank Jateng (Jawa Tengah)',                 'code' => '113'],
        ['name' => 'Bank Jatim (Jawa Timur)',                   'code' => '114'],
        ['name' => 'Bank Nagari (Sumatera Barat)',              'code' => '118'],
        ['name' => 'Bank Riau Kepri Syariah',                   'code' => '119'],
        ['name' => 'Bank Sumselbabel (Sumatera Selatan & Babel)', 'code' => '120'],
        ['name' => 'Bank Kalbar (Kalimantan Barat)',            'code' => '123'],
        ['name' => 'Bank Kaltimtara (Kalimantan Timur & Utara)', 'code' => '124'],
        ['name' => 'Bank Kalteng (Kalimantan Tengah)',          'code' => '125'],
        ['name' => 'Bank Sulselbar (Sulawesi Selatan & Barat)', 'code' => '126'],
        ['name' => 'Bank Sulutgo (Sulawesi Utara & Gorontalo)', 'code' => '127'],
        ['name' => 'Bank BPD Bali',                             'code' => '129'],
        ['name' => 'Bank Maluku Malut',                         'code' => '131'],
        ['name' => 'Bank Papua',                                'code' => '132'],
        ['name' => 'Bank Sumut (Sumatera Utara)',               'code' => '117'],
        ['name' => 'Bank Banten',                               'code' => '137'],
        ['name' => 'Bank Sleman',                               'code' => '622'],

        // ── Bank Syariah ───────────────────────────────────────────────────
        ['name' => 'Bank Syariah Indonesia (BSI)',              'code' => '451'],
        ['name' => 'Bank Muamalat Indonesia',                   'code' => '147'],
        ['name' => 'Bank BCA Syariah',                          'code' => '536'],
        ['name' => 'Bank BJB Syariah',                          'code' => '425'],
        ['name' => 'Bank KB Syariah Bukopin',                   'code' => '521'],
        ['name' => 'Bank Mega Syariah',                         'code' => '506'],
        ['name' => 'Bank BTPN Syariah',                         'code' => '547'],
        ['name' => 'Bank Victoria Syariah',                     'code' => '405'],
        ['name' => 'Bank Aceh Syariah',                         'code' => '116'],
        ['name' => 'Panin Dubai Syariah Bank',                  'code' => '517'],

        // ── Bank Digital ───────────────────────────────────────────────────
        ['name' => 'Allo Bank',                                 'code' => '567'],
        ['name' => 'Amar Bank',                                 'code' => '531'],
        ['name' => 'Bank Digital BCA (blu)',                    'code' => '501'],
        ['name' => 'Bank Neo Commerce (BNC)',                   'code' => '490'],
        ['name' => 'Bank Saqu',                                 'code' => '472'],
        ['name' => 'Krom Bank',                                 'code' => '459'],

        // ── Bank Asing ─────────────────────────────────────────────────────
        ['name' => 'Bank HSBC Indonesia',                       'code' => '087'],
        ['name' => 'Bank DBS Indonesia',                        'code' => '046'],
        ['name' => 'Bank CTBC Indonesia',                       'code' => '949'],
        ['name' => 'Bank of China Jakarta Branch',              'code' => '069'],
        ['name' => 'Bank Shinhan Indonesia',                    'code' => '152'],
        ['name' => 'MUFG Bank (Bank of Tokyo-Mitsubishi)',      'code' => '042'],
        ['name' => 'Standard Chartered Bank',                   'code' => '050'],
        ['name' => 'IBK Bank Indonesia',                        'code' => '945'],
        ['name' => 'Prima Master Bank',                         'code' => '520'],
    ];

    public function run(): void
    {
        // Seeder ini hanya menyediakan konstanta BANKS untuk digunakan di form.
        // Tidak insert ke database — bank_name di BankAccount adalah free-text
        // yang dipilih dari dropdown ini.
        $this->command->info('IndonesianBankSeeder: ' . count(self::$BANKS) . ' bank tersedia sebagai referensi dropdown.');
    }
}
