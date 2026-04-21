<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add unit_id ke inventory_items untuk track unit yang digunakan.
     * Quantity akan selalu dalam unit yang di-specify.
     */
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('unit_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('units')
                  ->restrictOnDelete()
                  ->comment('Unit yang digunakan untuk quantity (FK ke units)');
            
            $table->index('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropIndex(['unit_id']);
            $table->dropColumn('unit_id');
        });
    }
};
