<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            // Drop the old unique constraint (PO + approver + level)
            $table->dropUnique(['purchase_order_id', 'approver_id', 'level']);

            // Drop the existing FK so we can modify the column
            $table->dropForeign(['approver_id']);

            // Make approver_id nullable — approvals are created in "pending" state without an approver
            $table->foreignId('approver_id')->nullable()->change();

            // Re-add FK
            $table->foreign('approver_id')->references('id')->on('users')->restrictOnDelete();

            // New unique: one approval per PO per level (regardless of approver)
            $table->unique(['purchase_order_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropUnique(['purchase_order_id', 'level']);
            $table->dropForeign(['approver_id']);
            $table->foreignId('approver_id')->change();
            $table->foreign('approver_id')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['purchase_order_id', 'approver_id', 'level']);
        });
    }
};
