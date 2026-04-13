<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Harga Beli (Cost Price) - dari supplier
            $table->decimal('cost_price', 15, 2)->default(0)->after('price')
                ->comment('Harga beli dari supplier');
            
            // Harga Jual (Selling Price) - ke customer
            $table->decimal('selling_price', 15, 2)->default(0)->after('cost_price')
                ->comment('Harga jual ke customer');
            
            // Diskon Persentase
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('selling_price')
                ->comment('Persentase diskon (0-100)');
            
            // Diskon Nominal (calculated from percentage)
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percentage')
                ->comment('Nominal diskon dalam rupiah');
            
            // Add indexes for reporting
            $table->index('cost_price');
            $table->index('selling_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['cost_price']);
            $table->dropIndex(['selling_price']);
            $table->dropColumn([
                'cost_price',
                'selling_price',
                'discount_percentage',
                'discount_amount',
            ]);
        });
    }
};
