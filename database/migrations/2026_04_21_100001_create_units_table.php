<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create units master table untuk normalisasi satuan produk.
     * Mendukung multi-unit system dengan conversion.
     */
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('Nama unit: Pcs, Box, Strip, dll');
            $table->string('symbol', 10)->nullable()->comment('Simbol: pcs, box, strip');
            $table->enum('type', ['base', 'packaging', 'volume', 'weight', 'bundle'])
                  ->comment('Tipe unit untuk grouping');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
