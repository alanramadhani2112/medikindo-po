<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fixes the rate column in tax_configurations to accommodate both:
     * - PPN rates (e.g., 11.00, 12.00) — small values
     * - e-Meterai threshold (e.g., 5000000.00) — large values
     * 
     * Changed from DECIMAL(5,2) to DECIMAL(15,2)
     */
    public function up(): void
    {
        Schema::table('tax_configurations', function (Blueprint $table) {
            $table->decimal('rate', 15, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_configurations', function (Blueprint $table) {
            $table->decimal('rate', 5, 2)->change();
        });
    }
};
