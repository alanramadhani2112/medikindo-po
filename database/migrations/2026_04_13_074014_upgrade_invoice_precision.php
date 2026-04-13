<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Upgrade monetary fields from decimal(15,2) to decimal(18,2)
     * Add new financial tracking columns for pharmaceutical-grade precision
     */
    public function up(): void
    {
        // Upgrade supplier_invoices table
        Schema::table('supplier_invoices', function (Blueprint $table) {
            // Upgrade existing monetary fields to decimal(18,2)
            $table->decimal('total_amount', 18, 2)->change();
            $table->decimal('paid_amount', 18, 2)->change();
            
            // Add new financial tracking columns
            $table->decimal('subtotal_amount', 18, 2)->nullable()->after('total_amount');
            $table->decimal('discount_amount', 18, 2)->default(0)->after('subtotal_amount');
            $table->decimal('tax_amount', 18, 2)->default(0)->after('discount_amount');
            
            // Add optimistic locking version column
            $table->unsignedInteger('version')->default(0)->after('status');
        });

        // Upgrade customer_invoices table
        Schema::table('customer_invoices', function (Blueprint $table) {
            // Upgrade existing monetary fields to decimal(18,2)
            $table->decimal('total_amount', 18, 2)->change();
            $table->decimal('paid_amount', 18, 2)->change();
            
            // Add new financial tracking columns
            $table->decimal('subtotal_amount', 18, 2)->nullable()->after('total_amount');
            $table->decimal('discount_amount', 18, 2)->default(0)->after('subtotal_amount');
            $table->decimal('tax_amount', 18, 2)->default(0)->after('discount_amount');
            
            // Add optimistic locking version column
            $table->unsignedInteger('version')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     * 
     * Rollback to decimal(15,2) and remove new columns
     */
    public function down(): void
    {
        // Rollback supplier_invoices table
        Schema::table('supplier_invoices', function (Blueprint $table) {
            // Downgrade monetary fields back to decimal(15,2)
            $table->decimal('total_amount', 15, 2)->change();
            $table->decimal('paid_amount', 15, 2)->change();
            
            // Remove new columns
            $table->dropColumn(['subtotal_amount', 'discount_amount', 'tax_amount', 'version']);
        });

        // Rollback customer_invoices table
        Schema::table('customer_invoices', function (Blueprint $table) {
            // Downgrade monetary fields back to decimal(15,2)
            $table->decimal('total_amount', 15, 2)->change();
            $table->decimal('paid_amount', 15, 2)->change();
            
            // Remove new columns
            $table->dropColumn(['subtotal_amount', 'discount_amount', 'tax_amount', 'version']);
        });
    }
};
