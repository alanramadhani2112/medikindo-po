<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan field yang hilang untuk suppliers:
     * - license_expiry_date: tanggal kadaluarsa izin
     * - is_authorized_narcotic: izin distribusi narkotika
     * - unique constraint untuk license_number
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->date('license_expiry_date')->nullable()->after('license_number')
                  ->comment('Tanggal kadaluarsa izin distribusi');
            $table->boolean('is_authorized_narcotic')->default(false)->after('license_expiry_date')
                  ->comment('Memiliki izin distribusi narkotika');
            
            $table->index('license_expiry_date');
            $table->index('is_authorized_narcotic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['license_expiry_date']);
            $table->dropIndex(['is_authorized_narcotic']);
            $table->dropColumn(['license_expiry_date', 'is_authorized_narcotic']);
        });
    }
};
