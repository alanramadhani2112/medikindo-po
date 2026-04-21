<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Standardize payment fields across all payment-related tables.
 *
 * Changes:
 * 1. payments: add bank_account_id (FK), bank_name_manual, description, surcharge_amount, surcharge_percentage
 * 2. payment_proofs: add payment_method, bank_account_id, bank_name_manual
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Standardize `payments` table ──────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            // Bank reference — link to Medikindo's own bank accounts
            $table->foreignId('bank_account_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('bank_accounts')
                ->nullOnDelete();

            // Manual bank name (for cases where bank is not in system, e.g. supplier's bank)
            $table->string('bank_name_manual', 100)->nullable()->after('bank_account_id');

            // Description / notes
            $table->text('description')->nullable()->after('reference');

            // Surcharge fields (biaya tambahan atas metode pembayaran tertentu)
            $table->decimal('surcharge_amount', 15, 2)->default(0)->after('description');
            $table->decimal('surcharge_percentage', 5, 2)->default(0)->after('surcharge_amount');
        });

        // ── 2. Standardize `payment_proofs` table ────────────────────────────
        Schema::table('payment_proofs', function (Blueprint $table) {
            // Payment method (how RS/Klinik paid)
            $table->string('payment_method', 50)->nullable()->after('bank_reference');

            // Bank account used (Medikindo's receiving bank)
            $table->foreignId('bank_account_id')
                ->nullable()
                ->after('payment_method')
                ->constrained('bank_accounts')
                ->nullOnDelete();

            // Sender bank name (RS/Klinik's bank — manual input)
            $table->string('sender_bank_name', 100)->nullable()->after('bank_account_id');

            // Recall reason (already in model but may be missing in DB)
            if (!Schema::hasColumn('payment_proofs', 'recall_reason')) {
                $table->text('recall_reason')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('payment_proofs', 'recalled_at')) {
                $table->timestamp('recalled_at')->nullable()->after('recall_reason');
            }
            if (!Schema::hasColumn('payment_proofs', 'correction_of_id')) {
                $table->foreignId('correction_of_id')->nullable()->after('recalled_at')
                    ->constrained('payment_proofs')->nullOnDelete();
            }
            if (!Schema::hasColumn('payment_proofs', 'payment_type')) {
                $table->string('payment_type', 20)->default('full')->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn(['bank_account_id', 'bank_name_manual', 'description', 'surcharge_amount', 'surcharge_percentage']);
        });

        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropColumn(['payment_method', 'bank_account_id', 'sender_bank_name']);
        });
    }
};
