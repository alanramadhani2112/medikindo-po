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
        // Add goods_receipt_id to supplier_invoices (if not exists)
        if (!Schema::hasColumn('supplier_invoices', 'goods_receipt_id')) {
            Schema::table('supplier_invoices', function (Blueprint $table) {
                $table->foreignId('goods_receipt_id')
                      ->nullable()
                      ->after('purchase_order_id')
                      ->constrained('goods_receipts')
                      ->onDelete('restrict');
                
                $table->index('goods_receipt_id');
            });
        }

        // Add goods_receipt_id to customer_invoices (if not exists)
        if (!Schema::hasColumn('customer_invoices', 'goods_receipt_id')) {
            Schema::table('customer_invoices', function (Blueprint $table) {
                $table->foreignId('goods_receipt_id')
                      ->nullable()
                      ->after('purchase_order_id')
                      ->constrained('goods_receipts')
                      ->onDelete('restrict');
                
                $table->index('goods_receipt_id');
            });
        }

        // Add goods_receipt_item_id to supplier_invoice_line_items
        Schema::table('supplier_invoice_line_items', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_invoice_line_items', 'goods_receipt_item_id')) {
                $table->foreignId('goods_receipt_item_id')
                      ->nullable()
                      ->after('supplier_invoice_id')
                      ->constrained('goods_receipt_items')
                      ->onDelete('restrict');
                
                $table->index('goods_receipt_item_id');
            }
            
            // Add batch and expiry tracking (read-only from GR)
            if (!Schema::hasColumn('supplier_invoice_line_items', 'batch_no')) {
                $table->string('batch_no', 100)->nullable()->after('product_sku');
            }
            if (!Schema::hasColumn('supplier_invoice_line_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('batch_no');
            }
        });

        // Add goods_receipt_item_id to customer_invoice_line_items
        Schema::table('customer_invoice_line_items', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_invoice_line_items', 'goods_receipt_item_id')) {
                $table->foreignId('goods_receipt_item_id')
                      ->nullable()
                      ->after('customer_invoice_id')
                      ->constrained('goods_receipt_items')
                      ->onDelete('restrict');
                
                $table->index('goods_receipt_item_id');
            }
            
            // Add batch and expiry tracking (read-only from GR)
            if (!Schema::hasColumn('customer_invoice_line_items', 'batch_no')) {
                $table->string('batch_no', 100)->nullable()->after('product_sku');
            }
            if (!Schema::hasColumn('customer_invoice_line_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('batch_no');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_invoice_line_items', function (Blueprint $table) {
            $table->dropForeign(['goods_receipt_item_id']);
            $table->dropColumn(['goods_receipt_item_id', 'batch_no', 'expiry_date']);
        });

        Schema::table('supplier_invoice_line_items', function (Blueprint $table) {
            $table->dropForeign(['goods_receipt_item_id']);
            $table->dropColumn(['goods_receipt_item_id', 'batch_no', 'expiry_date']);
        });

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeign(['goods_receipt_id']);
            $table->dropColumn('goods_receipt_id');
        });

        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->dropForeign(['goods_receipt_id']);
            $table->dropColumn('goods_receipt_id');
        });
    }
};
