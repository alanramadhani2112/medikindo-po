<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('cn_number')->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_invoice_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_invoice_id')->nullable()->constrained()->cascadeOnDelete();
            
            $table->enum('type', ['return', 'discount', 'correction', 'cancellation']);
            $table->enum('status', ['draft', 'issued', 'applied', 'cancelled'])->default('draft');
            
            $table->string('reason');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            
            $table->foreignId('issued_by')->nullable()->constrained('users');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('applied_at')->nullable();
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['organization_id', 'status']);
            $table->index(['customer_invoice_id']);
            $table->index(['supplier_invoice_id']);
            $table->index(['cn_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};