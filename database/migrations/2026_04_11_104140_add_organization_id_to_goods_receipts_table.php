<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('goods_receipts', 'organization_id')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->foreignId('organization_id')->nullable()->after('gr_number')->constrained('organizations')->restrictOnDelete();
            });

            // Data Migration: Fill organization_id from purchase_orders
            // SQLite-compatible: use subquery instead of JOIN
            if (DB::connection()->getDriverName() === 'sqlite') {
                DB::statement("
                    UPDATE goods_receipts
                    SET organization_id = (
                        SELECT organization_id 
                        FROM purchase_orders 
                        WHERE purchase_orders.id = goods_receipts.purchase_order_id
                    )
                    WHERE organization_id IS NULL
                      AND purchase_order_id IS NOT NULL
                ");
            } else {
                // MySQL/PostgreSQL: use JOIN syntax
                DB::statement("
                    UPDATE goods_receipts gr
                    JOIN purchase_orders po ON gr.purchase_order_id = po.id
                    SET gr.organization_id = po.organization_id
                    WHERE gr.organization_id IS NULL
                ");
            }
            
            // Make it non-nullable after migration if all rows are filled
            // (Keeping it nullable for safety if some GRs don't have POs, though they should)
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
