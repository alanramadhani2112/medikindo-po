<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $query) {
            $query->id();
            $query->foreignId('customer_invoice_id')->constrained()->cascadeOnDelete();
            $query->foreignId('submitted_by')->constrained('users')->cascadeOnDelete();
            $query->decimal('amount', 15, 2);
            $query->date('payment_date');
            $query->string('bank_reference')->nullable();
            $query->text('notes')->nullable();
            $query->string('status')->default('submitted');
            $query->foreignId('verified_by')->nullable()->constrained('users');
            $query->timestamp('verified_at')->nullable();
            $query->foreignId('approved_by')->nullable()->constrained('users');
            $query->timestamp('approved_at')->nullable();
            $query->text('rejection_reason')->nullable();
            $query->timestamps();
        });

        Schema::create('payment_documents', function (Blueprint $query) {
            $query->id();
            $query->foreignId('payment_proof_id')->constrained()->cascadeOnDelete();
            $query->string('file_path');
            $query->string('original_filename');
            $query->string('mime_type')->nullable();
            $query->bigInteger('file_size')->nullable();
            $query->foreignId('uploaded_by')->constrained('users');
            $query->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_documents');
        Schema::dropIfExists('payment_proofs');
    }
};
