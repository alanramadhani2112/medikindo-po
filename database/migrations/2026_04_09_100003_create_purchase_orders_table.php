<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique()->comment('System-generated PO number');
            $table->foreignId('clinic_id')->constrained('clinics')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'sent_to_supplier',
            ])->default('draft');

            $table->boolean('has_narcotics')->default(false)->comment('True if any item is narcotic');
            $table->boolean('requires_extra_approval')->default(false);

            $table->decimal('total_amount', 15, 2)->default(0);
            $table->date('requested_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('po_number');
            $table->index('clinic_id');
            $table->index('supplier_id');
            $table->index('status');
            $table->index('has_narcotics');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
