<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan field untuk pengelolaan narkotika:
     * - narcotic_group: golongan narkotika (I, II, III)
     * - requires_sp: memerlukan Surat Pesanan
     * - requires_prescription: memerlukan resep dokter
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('narcotic_group', ['I', 'II', 'III'])->nullable()->after('is_narcotic')
                  ->comment('Golongan narkotika: I, II, atau III');
            $table->boolean('requires_sp')->default(false)->after('narcotic_group')
                  ->comment('Memerlukan Surat Pesanan');
            $table->boolean('requires_prescription')->default(false)->after('requires_sp')
                  ->comment('Memerlukan resep dokter');
            
            $table->index('narcotic_group');
            $table->index('requires_sp');
            $table->index('requires_prescription');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['narcotic_group']);
            $table->dropIndex(['requires_sp']);
            $table->dropIndex(['requires_prescription']);
            $table->dropColumn(['narcotic_group', 'requires_sp', 'requires_prescription']);
        });
    }
};
