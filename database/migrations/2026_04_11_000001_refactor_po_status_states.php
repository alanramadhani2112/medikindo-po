<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1 — Add new timestamp columns
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->change(); // ensure varchar so we can freely change values
            $table->timestamp('shipped_at')->nullable()->after('sent_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->timestamp('completed_at')->nullable()->after('delivered_at');
        });

        // Step 2 — Data migrate: map old statuses to new spec
        DB::table('purchase_orders')->where('status', 'under_review')->update(['status' => 'submitted']);
        DB::table('purchase_orders')->where('status', 'sent_to_supplier')->update(['status' => 'shipped']);
        DB::table('purchase_orders')->where('status', 'partially_received')->update(['status' => 'delivered']);
        DB::table('purchase_orders')->where('status', 'received_in_full')->update(['status' => 'completed']);
    }

    public function down(): void
    {
        // Reverse data migration
        DB::table('purchase_orders')->where('status', 'completed')->update(['status' => 'received_in_full']);
        DB::table('purchase_orders')->where('status', 'delivered')->update(['status' => 'partially_received']);
        DB::table('purchase_orders')->where('status', 'shipped')->update(['status' => 'sent_to_supplier']);
        DB::table('purchase_orders')->where('status', 'submitted')->update(['status' => 'under_review']);

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['shipped_at', 'delivered_at', 'completed_at']);
        });
    }
};
