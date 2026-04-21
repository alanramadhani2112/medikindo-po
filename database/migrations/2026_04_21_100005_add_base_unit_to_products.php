<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add base_unit_id foreign key ke products table.
     * Setiap produk harus punya 1 base unit untuk inventory calculation.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('base_unit_id')
                  ->nullable()
                  ->after('unit')
                  ->constrained('units')
                  ->restrictOnDelete()
                  ->comment('Base unit untuk produk ini (FK ke units table)');
            
            $table->index('base_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['base_unit_id']);
            $table->dropIndex(['base_unit_id']);
            $table->dropColumn('base_unit_id');
        });
    }
};
