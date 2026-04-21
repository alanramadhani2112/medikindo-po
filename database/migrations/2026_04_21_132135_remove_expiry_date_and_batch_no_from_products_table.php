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
        Schema::table('products', function (Blueprint $table) {
            // Drop expiry_date and batch_no columns
            // These fields should be per-batch (in goods_receipt_items), not in master product
            // Reason: Regulatory compliance - one product can have multiple batches with different expiry dates
            $table->dropColumn(['expiry_date', 'batch_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restore columns if rollback is needed
            $table->date('expiry_date')->nullable()->after('is_active');
            $table->string('batch_no')->nullable()->after('expiry_date');
        });
    }
};
