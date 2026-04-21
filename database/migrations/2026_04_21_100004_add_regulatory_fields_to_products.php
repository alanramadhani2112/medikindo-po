<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add regulatory fields:
     * - registration_number: Nomor Izin Edar (NIE)
     * - manufacturer: Produsen
     * - sterilization: Metode sterilisasi
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Registration Information
            $table->string('registration_number', 50)
                  ->nullable()
                  ->unique()
                  ->after('sku')
                  ->comment('Nomor Izin Edar (NIE): AKL/AKD/AKP');
            
            $table->date('registration_date')
                  ->nullable()
                  ->after('registration_number')
                  ->comment('Tanggal izin edar diterbitkan');
            
            $table->date('registration_expiry')
                  ->nullable()
                  ->after('registration_date')
                  ->comment('Tanggal kadaluarsa izin edar');
            
            // Manufacturer Information
            $table->string('manufacturer', 255)
                  ->nullable()
                  ->after('supplier_id')
                  ->comment('Nama produsen/manufacturer');
            
            $table->string('country_of_origin', 100)
                  ->nullable()
                  ->after('manufacturer')
                  ->comment('Negara asal produk');
            
            // Sterilization
            $table->boolean('is_sterile')
                  ->default(false)
                  ->after('is_narcotic')
                  ->comment('Apakah produk steril');
            
            $table->enum('sterilization_method', ['ETO', 'Steam', 'Radiation', 'Other', 'None'])
                  ->nullable()
                  ->after('is_sterile')
                  ->comment('Metode sterilisasi');
            
            // Indexes
            $table->index('registration_number');
            $table->index('registration_expiry');
            $table->index('country_of_origin');
            $table->index('is_sterile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['registration_number']);
            $table->dropIndex(['registration_expiry']);
            $table->dropIndex(['country_of_origin']);
            $table->dropIndex(['is_sterile']);
            
            $table->dropColumn([
                'registration_number',
                'registration_date',
                'registration_expiry',
                'manufacturer',
                'country_of_origin',
                'is_sterile',
                'sterilization_method',
            ]);
        });
    }
};
