<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enhance bank_accounts table for cashflow tracking.
 *
 * New fields:
 * - account_type: 'receive' | 'send' | 'both'
 *   - receive = hanya menerima uang masuk dari RS/Klinik
 *   - send    = hanya mengirim uang keluar ke Supplier
 *   - both    = bisa keduanya
 * - default_for_receive: bool — default bank untuk menerima pembayaran AR
 * - default_for_send: bool — default bank untuk mengirim pembayaran AP
 * - default_priority: 1|2|3 — urutan prioritas (max 3 default per tipe)
 * - current_balance: saldo saat ini (opsional, untuk tracking manual)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            // Account type: receive (AR), send (AP), or both
            $table->enum('account_type', ['receive', 'send', 'both'])
                ->default('both')
                ->after('is_default');

            // Separate defaults for receive vs send
            $table->boolean('default_for_receive')->default(false)->after('account_type');
            $table->boolean('default_for_send')->default(false)->after('default_for_receive');

            // Priority order among defaults (1 = highest, 3 = lowest)
            $table->unsignedTinyInteger('default_priority')->default(0)->after('default_for_send');

            // Optional: current balance for manual tracking
            $table->decimal('current_balance', 15, 2)->nullable()->after('default_priority');
            $table->timestamp('balance_updated_at')->nullable()->after('current_balance');
        });

        // Migrate existing is_default → default_for_receive + default_for_send
        \DB::table('bank_accounts')
            ->where('is_default', true)
            ->update([
                'default_for_receive' => true,
                'default_for_send'    => true,
                'default_priority'    => 1,
            ]);
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'account_type',
                'default_for_receive',
                'default_for_send',
                'default_priority',
                'current_balance',
                'balance_updated_at',
            ]);
        });
    }
};
