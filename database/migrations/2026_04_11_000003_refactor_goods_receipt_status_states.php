<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete()->after('received_by');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
        });

        // Map existing 'draft' → 'pending', 'completed' → 'completed'
        DB::table('goods_receipts')->where('status', 'draft')->update(['status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['confirmed_by', 'confirmed_at']);
            $table->string('status', 30)->default('draft')->change();
        });

        DB::table('goods_receipts')->where('status', 'pending')->update(['status' => 'draft']);
    }
};
