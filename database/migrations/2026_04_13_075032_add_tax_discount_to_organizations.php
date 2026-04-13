<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add default tax rate and discount percentage to organizations table
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Default tax rate (e.g., 11.00 for 11% PPN in Indonesia)
            $table->decimal('default_tax_rate', 5, 2)->nullable()->after('address');
            
            // Default discount percentage (e.g., 5.00 for 5% discount)
            $table->decimal('default_discount_percentage', 5, 2)->nullable()->after('default_tax_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['default_tax_rate', 'default_discount_percentage']);
        });
    }
};
