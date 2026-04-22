<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            // Add product_id as a direct denormalized reference for performance
            // (avoids join through purchase_order_items on every invoice/PDF query)
            $table->foreignId('product_id')
                ->nullable()
                ->after('purchase_order_item_id')
                ->constrained('products')
                ->nullOnDelete();

            $table->index('product_id');
        });

        // Backfill existing rows: resolve product_id via purchase_order_items
        // SQLite-compatible: use subquery instead of JOIN
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('
                UPDATE goods_receipt_items
                SET product_id = (
                    SELECT product_id
                    FROM purchase_order_items
                    WHERE purchase_order_items.id = goods_receipt_items.purchase_order_item_id
                )
                WHERE product_id IS NULL
                  AND purchase_order_item_id IS NOT NULL
            ');
        } else {
            DB::statement('
                UPDATE goods_receipt_items gri
                INNER JOIN purchase_order_items poi ON poi.id = gri.purchase_order_item_id
                SET gri.product_id = poi.product_id
                WHERE gri.product_id IS NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropIndex(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
