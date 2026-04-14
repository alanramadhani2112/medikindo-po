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
        Schema::table('supplier_invoices', function (Blueprint $table) {
            // Add distributor invoice reference fields
            $table->string('distributor_invoice_number')->nullable()->after('invoice_number');
            $table->date('distributor_invoice_date')->nullable()->after('distributor_invoice_number');
            
            // Add index for faster lookup
            $table->index('distributor_invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->dropIndex(['distributor_invoice_number']);
            $table->dropColumn(['distributor_invoice_number', 'distributor_invoice_date']);
        });
    }
};
