<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('doc_type', 10)->comment('PO, GR, INV');
            $table->string('org_code', 20)->comment('Organization code');
            $table->unsignedSmallInteger('year')->comment('YYYY');
            $table->unsignedTinyInteger('month')->comment('MM');
            $table->unsignedInteger('last_number')->default(0)->comment('Last sequence number');
            $table->timestamps();

            // Unique constraint: one sequence per doc_type + org_code + year + month
            $table->unique(['doc_type', 'org_code', 'year', 'month'], 'doc_seq_unique');

            // Indexes for performance
            $table->index(['doc_type', 'org_code', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
