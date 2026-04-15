<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the tax_configurations table for dynamic PPN rates and e-Meterai thresholds.
     * Eliminates hardcoded tax rates — changes can be applied without code deployment.
     */
    public function up(): void
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('rate', 15, 2);
            $table->boolean('is_default')->default(false);
            $table->date('effective_date');
            $table->text('description')->nullable();
            $table->timestamps();

            // Index for active PPN rate lookup
            $table->index(['is_default', 'effective_date']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_configurations');
    }
};
