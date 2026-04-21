<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            // Surcharge percentage — e.g. 5% surcharge on Rp 300.000 = Rp 15.000 extra
            $table->decimal('surcharge_percentage', 5, 2)->default(0)->after('surcharge');
        });
    }

    public function down(): void
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropColumn('surcharge_percentage');
        });
    }
};
