<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL: This migration enforces that ALL invoices MUST have a Goods Receipt.
     * This prevents invoice creation without actual goods received.
     */
    public function up(): void
    {
        // First, update any existing NULL values to prevent constraint violation
        // In production, you should handle this more carefully based on business rules
        DB::statement('UPDATE supplier_invoices SET goods_receipt_id = (SELECT id FROM goods_receipts WHERE purchase_order_id = supplier_invoices.purchase_order_id LIMIT 1) WHERE goods_receipt_id IS NULL');
        DB::statement('UPDATE customer_invoices SET goods_receipt_id = (SELECT id FROM goods_receipts WHERE purchase_order_id = customer_invoices.purchase_order_id LIMIT 1) WHERE goods_receipt_id IS NULL');

        // Make goods_receipt_id NOT NULL for supplier_invoices
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('goods_receipt_id')->nullable(false)->change();
        });

        // Make goods_receipt_id NOT NULL for customer_invoices
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('goods_receipt_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('goods_receipt_id')->nullable()->change();
        });

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('goods_receipt_id')->nullable()->change();
        });
    }
};
