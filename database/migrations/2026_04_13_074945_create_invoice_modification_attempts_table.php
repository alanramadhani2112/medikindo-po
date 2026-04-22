<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create table to log all attempts to modify immutable invoice data
     */
    public function up(): void
    {
        Schema::create('invoice_modification_attempts', function (Blueprint $table) {
            $table->id();
            
            // Invoice identification
            $table->enum('invoice_type', ['supplier', 'customer']);
            $table->unsignedBigInteger('invoice_id');
            
            // User who attempted the modification
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Attempt details
            $table->timestamp('attempted_at');
            $table->json('attempted_changes'); // Store field changes as JSON
            $table->text('rejection_reason');
            
            // Network information for security audit
            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->text('user_agent')->nullable();
            
            // Indexes for efficient querying
            $table->index(['invoice_type', 'invoice_id']);
            $table->index('user_id');
            $table->index('attempted_at');
            
            // No timestamps() - this table is append-only, no updates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_modification_attempts');
    }
};
