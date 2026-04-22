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

        // Fix supplier_invoices: drop any existing SET NULL FK first, then make nullable (safe approach)
        // We keep goods_receipt_id nullable to avoid conflicts with ON DELETE SET NULL constraints
        // SQLite doesn't have information_schema, so skip FK introspection for SQLite
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $siFKs = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'supplier_invoices'
                AND COLUMN_NAME = 'goods_receipt_id'
                AND REFERENCED_TABLE_NAME = 'goods_receipts'
            ");

            foreach ($siFKs as $fk) {
                try {
                    Schema::table('supplier_invoices', function (Blueprint $table) use ($fk) {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    });
                } catch (\Throwable $e) {
                    // FK may not exist, continue
                }
            }
        }

        // Re-add FK with RESTRICT (no SET NULL conflict)
        if (Schema::hasColumn('supplier_invoices', 'goods_receipt_id')) {
            Schema::table('supplier_invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('goods_receipt_id')->nullable()->change();
            });
            try {
                Schema::table('supplier_invoices', function (Blueprint $table) {
                    $table->foreign('goods_receipt_id')
                          ->references('id')
                          ->on('goods_receipts')
                          ->onDelete('set null');
                });
            } catch (\Throwable $e) {
                // FK already exists, skip
            }
        }

        // Fix customer_invoices: same approach
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $ciFKs = DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'customer_invoices'
                AND COLUMN_NAME = 'goods_receipt_id'
                AND REFERENCED_TABLE_NAME = 'goods_receipts'
            ");

            foreach ($ciFKs as $fk) {
                try {
                    Schema::table('customer_invoices', function (Blueprint $table) use ($fk) {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    });
                } catch (\Throwable $e) {
                    // FK may not exist, continue
                }
            }
        }

        if (Schema::hasColumn('customer_invoices', 'goods_receipt_id')) {
            Schema::table('customer_invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('goods_receipt_id')->nullable()->change();
            });
            try {
                Schema::table('customer_invoices', function (Blueprint $table) {
                    $table->foreign('goods_receipt_id')
                          ->references('id')
                          ->on('goods_receipts')
                          ->onDelete('set null');
                });
            } catch (\Throwable $e) {
                // FK already exists, skip
            }
        }
    }

    public function down(): void
    {
        // No-op: reversing this migration is not needed
    }
};
