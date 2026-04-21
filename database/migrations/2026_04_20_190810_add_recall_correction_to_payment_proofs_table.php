<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            // For Recall feature
            $table->text('recall_reason')->nullable()->after('rejection_reason');
            $table->timestamp('recalled_at')->nullable()->after('recall_reason');

            // For Super Admin correction (links new proof to the one it replaces)
            $table->foreignId('correction_of_id')
                  ->nullable()
                  ->after('recalled_at')
                  ->constrained('payment_proofs')
                  ->nullOnDelete();

            // Also expand the status enum to include 'recalled'
            // Note: MySQL ALTER ENUM — must list ALL values
            $table->enum('status', ['submitted', 'verified', 'approved', 'rejected', 'recalled'])
                  ->default('submitted')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropForeign(['correction_of_id']);
            $table->dropColumn(['recall_reason', 'recalled_at', 'correction_of_id']);
            $table->enum('status', ['submitted', 'verified', 'approved', 'rejected'])
                  ->default('submitted')
                  ->change();
        });
    }
};
