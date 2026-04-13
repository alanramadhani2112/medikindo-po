<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->enum('type', ['incoming', 'outgoing']);
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->string('status', 30)->default('pending'); // pending, completed, failed
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('supplier_invoice_id')->nullable()->constrained('supplier_invoices')->nullOnDelete();
            $table->foreignId('customer_invoice_id')->nullable()->constrained('customer_invoices')->nullOnDelete();
            $table->decimal('allocated_amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::dropIfExists('payments');
    }
};
