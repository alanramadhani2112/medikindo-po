<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create product_units pivot table untuk many-to-many relationship
     * antara products dan units dengan conversion ratio.
     */
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('conversion_to_base', 10, 4)->default(1.0000)
                  ->comment('Conversion ratio ke base unit. Contoh: 1 Box = 50 Pcs → 50.0000');
            $table->boolean('is_base_unit')->default(false)
                  ->comment('Flag untuk base unit (hanya 1 per product)');
            $table->boolean('is_default_purchase')->default(false)
                  ->comment('Unit default untuk pembelian');
            $table->boolean('is_default_sales')->default(false)
                  ->comment('Unit default untuk penjualan');
            $table->string('barcode', 100)->nullable()
                  ->comment('Barcode khusus untuk unit ini (optional)');
            $table->timestamps();
            
            // Unique constraint: 1 product tidak bisa punya unit yang sama 2x
            $table->unique(['product_id', 'unit_id'], 'unique_product_unit');
            
            $table->index('product_id');
            $table->index('unit_id');
            $table->index('is_base_unit');
            $table->index('is_default_purchase');
            $table->index('is_default_sales');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
