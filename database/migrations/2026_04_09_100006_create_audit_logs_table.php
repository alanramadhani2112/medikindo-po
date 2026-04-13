<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->comment('e.g. po.created, po.approved, po.sent_to_supplier');
            $table->string('entity_type', 100)->comment('e.g. PurchaseOrder, Approval');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable()->comment('Snapshot of relevant data at the time of action');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('occurred_at')->useCurrent();

            $table->index('user_id');
            $table->index('action');
            $table->index(['entity_type', 'entity_id']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
