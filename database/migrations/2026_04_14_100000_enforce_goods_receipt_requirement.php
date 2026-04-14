<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fill any NULL goods_receipt_id with matching GR from same PO
        DB::statement('UPDATE supplier_invoices SET goods_receipt_id = (SELECT id FROM goods_receipts WHERE purchase_order_id = supplier_invoices.purchase_order_id LIMIT 1) WHERE goods_receipt_id IS NULL');
        DB::statement('UPDATE customer_invoices SET goods_receipt_id = (SELECT id FROM goods_receipts WHERE purchase_order_id = customer_invoices.purchase_order_id LIMIT 1) WHERE goods_receipt_id IS NULL');

        // supplier_invoices: already NOT NULL + FK from partial run, skip if already done
        $siNullable = DB::select("SELECT IS_NULLABLE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'supplier_invoices' AND COLUMN_NAME = 'goods_receipt_id'");
        if (!empty($siNullable) && $siNullable[0]->IS_NULLABLE === 'YES') {
            Schema::table('supplier_invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('goods_receipt_id')->nullable(false)->change();
                $table->foreign('goods_receipt_id')->references('id')->on('goods_receipts')->onDelete('restrict');
            });
        }

        // customer_invoices: drop existing SET NULL FK, make NOT NULL, add RESTRICT FK
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeign('customer_invoices_goods_receipt_id_foreign');
        });
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('goods_receipt_id')->nullable(false)->change();
            $table->foreign('goods_receipt_id')->references('id')->on('goods_receipts')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->dropForeign(['goods_receipt_id']);
            $table->unsignedBigInteger('goods_receipt_id')->nullable()->change();
        });

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeign(['goods_receipt_id']);
            $table->unsignedBigInteger('goods_receipt_id')->nullable()->change();
            $table->foreign('goods_receipt_id')->references('id')->on('goods_receipts')->onDelete('set null');
        });
    }
};
