<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->decimal('max_limit', 15, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->unique('organization_id'); 
        });

        Schema::create('credit_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->decimal('amount_used', 15, 2);
            $table->string('status', 30)->default('reserved'); // reserved, billed, released
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_usages');
        Schema::dropIfExists('credit_limits');
    }
};
