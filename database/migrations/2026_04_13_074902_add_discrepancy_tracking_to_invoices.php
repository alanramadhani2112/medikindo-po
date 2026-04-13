<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add columns to track discrepancies between invoice and purchase order amounts
     */
    public function up(): void
    {
        // Add discrepancy tracking to supplier_invoices
        Schema::table('supplier_invoices', function (Blueprint $table) {
            // Discrepancy detection flags
            $table->boolean('discrepancy_detected')->default(false)->after('version');
            $table->decimal('expected_total', 18, 2)->nullable()->after('discrepancy_detected');
            $table->decimal('variance_amount', 18, 2)->nullable()->after('expected_total');
            $table->decimal('variance_percentage', 5, 2)->nullable()->after('variance_amount');
            
            // Approval workflow fields
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('variance_percentage');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_reason')->nullable()->after('approved_at');
            
            // Rejection workflow fields
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });

        // Add discrepancy tracking to customer_invoices
        Schema::table('customer_invoices', function (Blueprint $table) {
            // Discrepancy detection flags
            $table->boolean('discrepancy_detected')->default(false)->after('version');
            $table->decimal('expected_total', 18, 2)->nullable()->after('discrepancy_detected');
            $table->decimal('variance_amount', 18, 2)->nullable()->after('expected_total');
            $table->decimal('variance_percentage', 5, 2)->nullable()->after('variance_amount');
            
            // Approval workflow fields
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('variance_percentage');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_reason')->nullable()->after('approved_at');
            
            // Rejection workflow fields
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('approval_reason');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejection_reason')->nullable()->after('rejected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove discrepancy tracking from supplier_invoices
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn([
                'discrepancy_detected',
                'expected_total',
                'variance_amount',
                'variance_percentage',
                'approved_by',
                'approved_at',
                'approval_reason',
                'rejected_by',
                'rejected_at',
                'rejection_reason',
            ]);
        });

        // Remove discrepancy tracking from customer_invoices
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn([
                'discrepancy_detected',
                'expected_total',
                'variance_amount',
                'variance_percentage',
                'approved_by',
                'approved_at',
                'approval_reason',
                'rejected_by',
                'rejected_at',
                'rejection_reason',
            ]);
        });
    }
};
