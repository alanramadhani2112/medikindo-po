<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan field fiscal yang hilang di organizations:
     * - city: kota
     * - province: provinsi
     * - is_authorized_narcotic: izin narkotika
     * 
     * Note: npwp, nik, customer_code, bank_accounts, default_tax_rate, default_discount_percentage
     * sudah ada di migration sebelumnya
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Tambah city dan province yang ada di form tapi tidak di DB
            $table->string('city', 100)->nullable()->after('address')
                  ->comment('Kota lokasi organisasi');
            $table->string('province', 100)->nullable()->after('city')
                  ->comment('Provinsi lokasi organisasi');
            
            // Tambah is_authorized_narcotic
            $table->boolean('is_authorized_narcotic')->default(false)->after('license_number')
                  ->comment('Memiliki izin pengelolaan narkotika');
            
            $table->index('city');
            $table->index('province');
            $table->index('is_authorized_narcotic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex(['city']);
            $table->dropIndex(['province']);
            $table->dropIndex(['is_authorized_narcotic']);
            $table->dropColumn(['city', 'province', 'is_authorized_narcotic']);
        });
    }
};
