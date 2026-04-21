<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add fields for manual payment entry to support:
     * - Bank Transfer/Virtual Account: sender bank name + account number
     * - Giro/Cek: giro number + due date + issuing bank
     * - Cash: receipt number
     * - File upload: payment proof document path
     * - Notes: additional notes
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Bank Transfer / Virtual Account fields
            $table->string('sender_bank_name', 100)->nullable()->after('payment_method');
            $table->string('sender_account_number', 50)->nullable()->after('sender_bank_name');
            
            // Giro/Cek fields
            $table->string('giro_number', 50)->nullable()->after('sender_account_number');
            $table->date('giro_due_date')->nullable()->after('giro_number');
            $table->string('issuing_bank', 100)->nullable()->after('giro_due_date');
            
            // Cash field
            $table->string('receipt_number', 50)->nullable()->after('issuing_bank');
            
            // File upload
            $table->string('payment_proof_path')->nullable()->after('receipt_number');
            
            // Notes (rename description to notes for consistency)
            $table->text('notes')->nullable()->after('payment_proof_path');
            
            // Bank account (already exists but ensure it's there)
            if (!Schema::hasColumn('payments', 'bank_account_id')) {
                $table->foreignId('bank_account_id')->nullable()->after('supplier_id')->constrained('bank_accounts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'sender_bank_name',
                'sender_account_number',
                'giro_number',
                'giro_due_date',
                'issuing_bank',
                'receipt_number',
                'payment_proof_path',
                'notes',
            ]);
        });
    }
};
