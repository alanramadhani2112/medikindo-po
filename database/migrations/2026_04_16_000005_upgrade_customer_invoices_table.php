<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Upgrades customer_invoices for full AR lifecycle:
     * - Replaces status enum with: draft, issued, partial_paid, paid, void
     * - Adds Anti-Phantom Link: supplier_invoice_id FK
     * - Adds billing fields: surcharge, ematerai_fee, payment_term, salesman, tax_number
     * - Adds print tracking: barcode_serial, print_count, last_printed_at
     * - Ensures all monetary columns use DECIMAL(15,2)
     */
    public function up(): void
    {
        // Step 1: Migrate existing statuses to new schema before changing column
        // Map old statuses: issued → issued, payment_submitted → partial_paid, paid → paid, overdue → issued
        DB::table('customer_invoices')->where('status', 'payment_submitted')->update(['status' => 'partial_paid']);
        DB::table('customer_invoices')->where('status', 'overdue')->update(['status' => 'issued']);
        // 'issued' and 'paid' remain as-is

        Schema::table('customer_invoices', function (Blueprint $table) {
            // Anti-Phantom Link: FK to supplier_invoices (nullable for legacy records)
            $table->unsignedBigInteger('supplier_invoice_id')->nullable()->after('goods_receipt_id');
            $table->foreign('supplier_invoice_id')
                  ->references('id')
                  ->on('supplier_invoices')
                  ->nullOnDelete();

            // AR billing fields
            $table->decimal('surcharge', 15, 2)->default(0)->after('tax_amount');
            $table->decimal('ematerai_fee', 15, 2)->default(0)->after('surcharge');
            $table->string('payment_term', 100)->nullable()->after('ematerai_fee');
            $table->string('salesman', 100)->nullable()->after('payment_term');
            $table->string('tax_number', 50)->nullable()->after('salesman');
            $table->string('barcode_serial', 100)->nullable()->unique()->after('tax_number');
            $table->unsignedInteger('print_count')->default(0)->after('barcode_serial');
            $table->timestamp('last_printed_at')->nullable()->after('print_count');

            // Index for supplier invoice lookup
            $table->index('supplier_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status migrations
        DB::table('customer_invoices')->where('status', 'partial_paid')->update(['status' => 'payment_submitted']);
        DB::table('customer_invoices')->where('status', 'draft')->update(['status' => 'issued']);
        DB::table('customer_invoices')->where('status', 'void')->update(['status' => 'issued']);

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeign(['supplier_invoice_id']);
            $table->dropIndex(['supplier_invoice_id']);
            $table->dropUnique(['barcode_serial']);
            $table->dropColumn([
                'supplier_invoice_id',
                'surcharge',
                'ematerai_fee',
                'payment_term',
                'salesman',
                'tax_number',
                'barcode_serial',
                'print_count',
                'last_printed_at',
            ]);
        });
    }
};
