<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company Identity
    |--------------------------------------------------------------------------
    | Data identitas perusahaan yang digunakan di seluruh dokumen PDF,
    | header aplikasi, dan komunikasi resmi.
    | Ubah nilai di .env untuk menyesuaikan tanpa menyentuh kode.
    |--------------------------------------------------------------------------
    */

    'name'        => env('COMPANY_NAME',        'PT. Mentari Medika Indonesia'),
    'name_upper'  => env('COMPANY_NAME_UPPER',  'PT. MENTARI MEDIKA INDONESIA'),
    'npwp'        => env('COMPANY_NPWP',        '01.234.567.8-901.000'),
    'pbf_license' => env('COMPANY_PBF_LICENSE', 'PBF-2024-001/KEMENKES'),
    'address'     => env('COMPANY_ADDRESS',     'Jl. Raya Farmasi No. 123, Jakarta Selatan 12345'),
    'phone'       => env('COMPANY_PHONE',       '(021) 1234-5678'),
    'fax'         => env('COMPANY_FAX',         '(021) 1234-5679'),
    'email'       => env('COMPANY_EMAIL',       'finance@mentarimedika.co.id'),
    'logo'        => env('COMPANY_LOGO',        'logo-medikindo.png'),

];
