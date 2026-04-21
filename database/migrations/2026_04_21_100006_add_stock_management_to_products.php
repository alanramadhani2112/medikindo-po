<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add stock management fields:
     * - min/max stock level
     * - reorder quantity
     * - storage requirements
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Stock Management
            $table->decimal('min_stock_level', 10, 2)
                  ->nullable()
                  ->after('is_active')
                  ->comment('Minimum stock level (reorder point) dalam base unit');
            
            $table->decimal('max_stock_level', 10, 2)
                  ->nullable()
                  ->after('min_stock_level')
                  ->comment('Maximum stock level dalam base unit');
            
            $table->decimal('reorder_quantity', 10, 2)
                  ->nullable()
                  ->after('max_stock_level')
                  ->comment('Quantity untuk reorder dalam base unit');
            
            // Storage Requirements
            $table->string('storage_temperature', 50)
                  ->nullable()
                  ->after('reorder_quantity')
                  ->comment('Suhu penyimpanan: 2-8°C, 15-25°C, dll');
            
            $table->text('storage_condition')
                  ->nullable()
                  ->after('storage_temperature')
                  ->comment('Kondisi penyimpanan: dry, cool, protected from light');
            
            $table->text('special_handling')
                  ->nullable()
                  ->after('storage_condition')
                  ->comment('Handling khusus: fragile, hazardous, dll');
            
            // Indexes
            $table->index('min_stock_level');
            $table->index('storage_temperature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['min_stock_level']);
            $table->dropIndex(['storage_temperature']);
            
            $table->dropColumn([
                'min_stock_level',
                'max_stock_level',
                'reorder_quantity',
                'storage_temperature',
                'storage_condition',
                'special_handling',
            ]);
        });
    }
};
