<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->enum('payment_type', ['full', 'partial'])
                  ->default('full')
                  ->after('amount')
                  ->comment('full = lunasi seluruh tagihan; partial = bayar sebagian');
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropColumn('payment_type');
        });
    }
};
