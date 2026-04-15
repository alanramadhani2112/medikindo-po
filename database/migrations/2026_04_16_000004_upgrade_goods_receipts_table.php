<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Upgrades goods_receipts to function as Delivery Orders (DO):
     * - do_number: Delivery Order document number (unique)
     * - delivered_at: actual delivery timestamp
     */
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->string('do_number', 50)->nullable()->unique()->after('gr_number');
            $table->timestamp('delivered_at')->nullable()->after('do_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropUnique(['do_number']);
            $table->dropColumn(['do_number', 'delivered_at']);
        });
    }
};
