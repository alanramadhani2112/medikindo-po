<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('approver_id')->constrained('users')->restrictOnDelete();
            $table->unsignedTinyInteger('level')->comment('Approval level: 1=standard, 2=narcotics');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('actioned_at')->nullable()->comment('When the decision was made');
            $table->timestamps();

            $table->index('purchase_order_id');
            $table->index('approver_id');
            $table->index('status');
            // Prevent duplicate approval records for same PO + approver + level
            $table->unique(['purchase_order_id', 'approver_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
