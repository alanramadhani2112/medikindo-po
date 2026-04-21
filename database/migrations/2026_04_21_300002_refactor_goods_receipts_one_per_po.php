<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforce 1 GR per PO:
 * - Add unique constraint on purchase_order_id in goods_receipts
 * - do_number moves to goods_receipt_deliveries (already done in migration above)
 * - Migrate existing GR data: merge duplicate GRs for same PO into one
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- 1. Migrate existing data: for each PO that has multiple GRs,
        //        keep the oldest GR and re-parent items to it, delete the rest ---
        $duplicatePOs = DB::table('goods_receipts')
            ->select('purchase_order_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('purchase_order_id')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicatePOs as $dup) {
            $otherIds = DB::table('goods_receipts')
                ->where('purchase_order_id', $dup->purchase_order_id)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            // Re-parent GR items to the kept GR
            DB::table('goods_receipt_items')
                ->whereIn('goods_receipt_id', $otherIds)
                ->update(['goods_receipt_id' => $dup->keep_id]);

            // Re-parent supplier invoices
            DB::table('supplier_invoices')
                ->whereIn('goods_receipt_id', $otherIds)
                ->update(['goods_receipt_id' => $dup->keep_id]);

            // Re-parent customer invoices
            DB::table('customer_invoices')
                ->whereIn('goods_receipt_id', $otherIds)
                ->update(['goods_receipt_id' => $dup->keep_id]);

            // Soft-delete the duplicate GRs
            DB::table('goods_receipts')
                ->whereIn('id', $otherIds)
                ->update(['deleted_at' => now()]);
        }

        // --- 2. For each PO, ensure the surviving GR has status = completed
        //        if all PO items are fully received, else partial ---
        $grs = DB::table('goods_receipts')->whereNull('deleted_at')->get();
        foreach ($grs as $gr) {
            $poItems = DB::table('purchase_order_items')
                ->where('purchase_order_id', $gr->purchase_order_id)
                ->get();

            $allFulfilled = true;
            foreach ($poItems as $poItem) {
                $received = DB::table('goods_receipt_items')
                    ->where('goods_receipt_id', $gr->id)
                    ->where('purchase_order_item_id', $poItem->id)
                    ->sum('quantity_received');

                if ($received < $poItem->quantity) {
                    $allFulfilled = false;
                    break;
                }
            }

            DB::table('goods_receipts')
                ->where('id', $gr->id)
                ->update(['status' => $allFulfilled ? 'completed' : 'partial']);
        }

        // --- 3. Add unique constraint: 1 GR per PO ---
        // Make purchase_order_id nullable first so soft-deleted rows can be nullified
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->change();
        });

        // Nullify purchase_order_id on soft-deleted GRs so unique constraint works
        // (MySQL unique index allows multiple NULLs)
        DB::table('goods_receipts')
            ->whereNotNull('deleted_at')
            ->update(['purchase_order_id' => null]);

        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->unique('purchase_order_id', 'gr_unique_per_po');
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropUnique('gr_unique_per_po');
        });
    }
};
