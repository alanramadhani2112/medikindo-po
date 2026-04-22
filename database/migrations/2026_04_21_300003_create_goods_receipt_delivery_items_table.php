<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates goods_receipt_delivery_items table.
 * (goods_receipt_deliveries was already created in migration 300001)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Table was already created in migration 300001 alongside goods_receipt_deliveries.
        // This migration is a no-op to avoid duplicate table creation.
        if (Schema::hasTable('goods_receipt_delivery_items')) {
            return;
        }

        Schema::create('goods_receipt_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_delivery_id')
                  ->constrained('goods_receipt_deliveries')
                  ->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')
                  ->constrained('purchase_order_items')
                  ->restrictOnDelete();
            $table->integer('quantity_received');
            $table->string('batch_no', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('condition', 50)->default('Good');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_delivery_items');
    }
};
