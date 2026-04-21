<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'partially_received' status to purchase_orders.
     * Also fixes inventory: stock is now added on every GR (partial or completed).
     * No schema change needed — status column is already varchar(30).
     */
    public function up(): void
    {
        // No schema change needed — status is already varchar(30)
        // Data migration: any PO in 'approved' that has at least one partial GR
        // should be moved to 'partially_received'
        DB::statement("
            UPDATE purchase_orders po
            SET po.status = 'partially_received'
            WHERE po.status = 'approved'
              AND EXISTS (
                  SELECT 1 FROM goods_receipts gr
                  WHERE gr.purchase_order_id = po.id
                    AND gr.deleted_at IS NULL
              )
        ");
    }

    public function down(): void
    {
        // Reverse: move partially_received back to approved
        DB::table('purchase_orders')
            ->where('status', 'partially_received')
            ->update(['status' => 'approved']);
    }
};
