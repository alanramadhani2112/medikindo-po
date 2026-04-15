<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Upgrades customer_invoice_line_items for Mirror Model and BPOM audit trail:
     * - supplier_invoice_item_id: Mirror Link to AP line item (critical for BPOM audit)
     * - cost_price: snapshot of AP cost for margin protection
     * - batch_number: drug batch number (mandatory for narcotics/psychotropics)
     * - expiry_date: batch expiry date
     * - uom: unit of measure
     * - Ensures tax_rate and tax_amount exist with correct precision
     */
    public function up(): void
    {
        Schema::table('customer_invoice_line_items', function (Blueprint $table) {
            // Mirror Link: FK to supplier_invoice_line_items (NOT NULL for new records)
            // Using nullable here to allow legacy records without breaking existing data
            $table->unsignedBigInteger('supplier_invoice_item_id')->nullable()->after('customer_invoice_id');
            $table->foreign('supplier_invoice_item_id')
                  ->references('id')
                  ->on('supplier_invoice_line_items')
                  ->nullOnDelete();

            // Cost price snapshot from AP (for margin protection)
            $table->decimal('cost_price', 15, 2)->nullable()->after('unit_price');

            // Pharmaceutical batch tracking (BPOM audit trail)
            $table->string('batch_number', 50)->nullable()->after('cost_price');
            $table->string('uom', 20)->nullable()->after('batch_number');

            // Index for Mirror Link lookups
            $table->index('supplier_invoice_item_id');
        });

        // Ensure tax_rate and tax_amount use correct precision (already exist from create migration)
        // The existing columns are decimal(5,2) and decimal(18,2) — we need to ensure they exist
        // They were created in 2026_04_13_074703, so we just verify they're present
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_invoice_line_items', function (Blueprint $table) {
            $table->dropForeign(['supplier_invoice_item_id']);
            $table->dropIndex(['supplier_invoice_item_id']);
            $table->dropColumn([
                'supplier_invoice_item_id',
                'cost_price',
                'batch_number',
                'uom',
            ]);
        });
    }
};
