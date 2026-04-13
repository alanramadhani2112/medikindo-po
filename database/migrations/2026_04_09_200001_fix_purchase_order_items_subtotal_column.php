<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replace the MySQL-only generated/stored column with a plain decimal column.
 * Subtotal will be calculated by the PurchaseOrderItem model observer instead,
 * making the schema portable for both MySQL (production) and SQLite (testing).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Drop the MySQL-specific stored generated column
            $table->dropColumn('subtotal');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Re-add as a plain decimal column; observer will keep it in sync
            $table->decimal('subtotal', 15, 2)->default(0)->after('unit_price');
        });

        // Back-fill existing rows
        DB::statement('UPDATE purchase_order_items SET subtotal = quantity * unit_price');
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('subtotal');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->storedAs('quantity * unit_price')->after('unit_price');
        });
    }
};
