<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('status', 30)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'sent_to_supplier',
            ])->default('draft')->change();
        });
    }
};
