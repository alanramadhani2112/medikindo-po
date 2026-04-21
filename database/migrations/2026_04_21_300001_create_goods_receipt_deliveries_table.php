<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Redesign: 1 PO → 1 GR → N Deliveries (pengiriman bertahap)
 *
 * goods_receipt_deliveries  = satu sesi pengiriman fisik (surat jalan)
 * goods_receipt_delivery_items = detail per produk per sesi pengiriman
 *
 * goods_receipt_items tetap ada untuk backward-compat dengan invoicing,
 * tapi sekarang diisi secara agregat dari semua delivery items.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipt_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->string('delivery_number', 100)->comment('Nomor surat jalan / DO dari supplier');
            $table->unsignedTinyInteger('delivery_sequence')->default(1)->comment('Urutan pengiriman ke-N');
            $table->date('received_date');
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->string('photo_path')->nullable()->comment('Foto bukti penerimaan (wajib)');
            $table->string('photo_original_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['goods_receipt_id', 'delivery_sequence'], 'grd_gr_seq_idx');
        });

        Schema::create('goods_receipt_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_delivery_id')->constrained('goods_receipt_deliveries')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->constrained('purchase_order_items')->restrictOnDelete();
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
        Schema::dropIfExists('goods_receipt_deliveries');
    }
};
