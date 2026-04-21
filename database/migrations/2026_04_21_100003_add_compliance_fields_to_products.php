<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add compliance fields untuk product classification:
     * - product_type: ALKES / ALKES_DIV / PKRT
     * - risk_class: A-D (ALKES) atau 1-3 (PKRT)
     * - intended_use: Tujuan penggunaan
     * - usage_method: single_use / reusable / sterilizable
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Product Classification
            $table->enum('product_type', ['ALKES', 'ALKES_DIV', 'PKRT'])
                  ->nullable()
                  ->after('category')
                  ->comment('Tipe produk: ALKES / ALKES_DIV / PKRT');
            
            $table->string('risk_class', 10)
                  ->nullable()
                  ->after('product_type')
                  ->comment('Risk class: A-D (ALKES) atau 1-3 (PKRT)');
            
            // Intended Use & Usage Method
            $table->text('intended_use')
                  ->nullable()
                  ->after('risk_class')
                  ->comment('Tujuan penggunaan produk');
            
            $table->enum('usage_method', ['single_use', 'reusable', 'sterilizable'])
                  ->nullable()
                  ->after('intended_use')
                  ->comment('Metode penggunaan');
            
            $table->string('target_user', 50)
                  ->nullable()
                  ->after('usage_method')
                  ->comment('Target pengguna: healthcare_professional / consumer / both');
            
            // Indexes
            $table->index('product_type');
            $table->index('risk_class');
            $table->index('usage_method');
            $table->index('target_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['product_type']);
            $table->dropIndex(['risk_class']);
            $table->dropIndex(['usage_method']);
            $table->dropIndex(['target_user']);
            
            $table->dropColumn([
                'product_type',
                'risk_class',
                'intended_use',
                'usage_method',
                'target_user',
            ]);
        });
    }
};
