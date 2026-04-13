<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create separate tables for invoice line items with full calculation details
     */
    public function up(): void
    {
        // Create supplier_invoice_line_items table
        Schema::create('supplier_invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_invoice_id')->constrained('supplier_invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name'); // Denormalized for immutability
            $table->string('product_sku')->nullable(); // Denormalized for reference
            
            // Quantity with 3 decimal places to support fractional quantities
            $table->decimal('quantity', 10, 3);
            
            // Pricing with pharmaceutical-grade precision
            $table->decimal('unit_price', 18, 2);
            
            // Discount fields (either percentage OR amount, not both)
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 18, 2)->default(0);
            
            // Tax fields
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            
            // Calculated line total
            $table->decimal('line_total', 18, 2);
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('supplier_invoice_id');
            $table->index('product_id');
        });

        // Create customer_invoice_line_items table
        Schema::create('customer_invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_invoice_id')->constrained('customer_invoices')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name'); // Denormalized for immutability
            $table->string('product_sku')->nullable(); // Denormalized for reference
            
            // Quantity with 3 decimal places to support fractional quantities
            $table->decimal('quantity', 10, 3);
            
            // Pricing with pharmaceutical-grade precision
            $table->decimal('unit_price', 18, 2);
            
            // Discount fields (either percentage OR amount, not both)
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 18, 2)->default(0);
            
            // Tax fields
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 18, 2)->default(0);
            
            // Calculated line total
            $table->decimal('line_total', 18, 2);
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('customer_invoice_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_invoice_line_items');
        Schema::dropIfExists('supplier_invoice_line_items');
    }
};
