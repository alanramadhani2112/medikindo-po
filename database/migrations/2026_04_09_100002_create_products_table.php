<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('name');
            $table->string('sku', 50)->unique()->comment('Stock Keeping Unit');
            $table->string('category', 100)->nullable();
            $table->string('unit', 30)->comment('e.g. pcs, box, vial');
            $table->decimal('price', 15, 2)->default(0);
            $table->boolean('is_narcotic')->default(false)->comment('Triggers additional approval level');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('is_narcotic');
            $table->index('category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
