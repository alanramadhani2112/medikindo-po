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
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            // Add batch and expiry tracking
            $table->string('batch_no', 100)->nullable()->after('quantity_received');
            $table->date('expiry_date')->nullable()->after('batch_no');
            $table->string('uom', 50)->nullable()->after('expiry_date');
            
            $table->index('batch_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropColumn(['batch_no', 'expiry_date', 'uom']);
        });
    }
};
