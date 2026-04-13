<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('clinic_id')->constrained('clinics')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('unpaid'); // unpaid, partial, paid
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('clinic_id')->constrained('clinics')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status', 30)->default('unpaid'); // unpaid, partial, paid
            $table->date('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_invoices');
        Schema::dropIfExists('supplier_invoices');
    }
};
