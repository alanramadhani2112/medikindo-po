<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplier_invoice_line_items', function (Blueprint $table) {
            if (!Schema::hasColumn('supplier_invoice_line_items', 'batch_number')) {
                $table->string('batch_number', 50)->nullable()->after('product_id');
            }
            if (!Schema::hasColumn('supplier_invoice_line_items', 'uom')) {
                $table->string('uom', 20)->nullable()->after('expiry_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplier_invoice_line_items', function (Blueprint $table) {
            if (Schema::hasColumn('supplier_invoice_line_items', 'batch_number')) {
                $table->dropColumn('batch_number');
            }
            if (Schema::hasColumn('supplier_invoice_line_items', 'uom')) {
                $table->dropColumn('uom');
            }
        });
    }
};
