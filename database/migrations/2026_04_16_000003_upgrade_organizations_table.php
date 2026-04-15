<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds fiscal data columns to organizations table:
     * - npwp: Nomor Pokok Wajib Pajak
     * - nik: Nomor Induk Kependudukan (for individual customers)
     * - customer_code: internal customer identifier (unique)
     * - bank_accounts: JSON array of bank account objects
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('npwp', 20)->nullable()->after('email');
            $table->string('nik', 16)->nullable()->after('npwp');
            $table->string('customer_code', 50)->nullable()->unique()->after('nik');
            $table->json('bank_accounts')->nullable()->after('customer_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropUnique(['customer_code']);
            $table->dropColumn(['npwp', 'nik', 'customer_code', 'bank_accounts']);
        });
    }
};
