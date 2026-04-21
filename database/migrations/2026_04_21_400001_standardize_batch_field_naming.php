<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Standardize batch field naming across all invoice line items tables.
     * 
     * BEFORE:
     * - supplier_invoice_line_items: batch_number
     * - customer_invoice_line_items: batch_number
     * - goods_receipt_items: batch_no
     * 
     * AFTER:
     * - All tables use: batch_no (consistent with GR source of truth)
     */
    public function up(): void
    {
        // Rename batch_number to batch_no in supplier_invoice_line_items
        if (Schema::hasColumn('supplier_invoice_line_items', 'batch_number')) {
            Schema::table('supplier_invoice_line_items', function (Blueprint $table) {
                $table->renameColumn('batch_number', 'batch_no');
            });
        }

        // Rename batch_number to batch_no in customer_invoice_line_items
        if (Schema::hasColumn('customer_invoice_line_items', 'batch_number')) {
            Schema::table('customer_invoice_line_items', function (Blueprint $table) {
                $table->renameColumn('batch_number', 'batch_no');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert batch_no to batch_number in supplier_invoice_line_items
        if (Schema::hasColumn('supplier_invoice_line_items', 'batch_no')) {
            Schema::table('supplier_invoice_line_items', function (Blueprint $table) {
                $table->renameColumn('batch_no', 'batch_number');
            });
        }

        // Revert batch_no to batch_number in customer_invoice_line_items
        if (Schema::hasColumn('customer_invoice_line_items', 'batch_no')) {
            Schema::table('customer_invoice_line_items', function (Blueprint $table) {
                $table->renameColumn('batch_no', 'batch_number');
            });
        }
    }
};
