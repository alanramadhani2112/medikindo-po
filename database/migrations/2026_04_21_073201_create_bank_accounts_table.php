<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 100);
            $table->string('account_number', 30)->unique();
            $table->string('account_holder_name', 100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->foreignId('bank_account_id')
                ->nullable()
                ->after('organization_id')
                ->constrained('bank_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\BankAccount::class);
            $table->dropColumn('bank_account_id');
        });

        Schema::dropIfExists('bank_accounts');
    }
};
