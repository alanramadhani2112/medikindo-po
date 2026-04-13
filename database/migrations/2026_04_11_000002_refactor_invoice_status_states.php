<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- customer_invoices ---
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->string('status', 30)->default('issued')->change();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('issued_at')->nullable()->after('issued_by');
            $table->string('payment_reference')->nullable()->after('issued_at');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_reference');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('payment_submitted_at');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });

        // Map old statuses to 'issued' (the base state for all existing records)
        DB::table('customer_invoices')->whereIn('status', ['unpaid', 'partial'])->update(['status' => 'issued']);
        DB::table('customer_invoices')->where('status', 'paid')->update(['status' => 'paid']); // keep paid as paid

        // --- supplier_invoices ---
        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->string('status', 30)->default('issued')->change();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('issued_at')->nullable()->after('issued_by');
            $table->string('payment_reference')->nullable()->after('issued_at');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_reference');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('payment_submitted_at');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });

        DB::table('supplier_invoices')->whereIn('status', ['unpaid', 'partial'])->update(['status' => 'issued']);
        DB::table('supplier_invoices')->where('status', 'paid')->update(['status' => 'paid']);
    }

    public function down(): void
    {
        Schema::table('customer_invoices', function (Blueprint $table) {
            $table->dropForeign(['issued_by']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['issued_by', 'issued_at', 'payment_reference', 'payment_submitted_at', 'verified_by', 'verified_at']);
            $table->string('status', 30)->default('unpaid')->change();
        });

        Schema::table('supplier_invoices', function (Blueprint $table) {
            $table->dropForeign(['issued_by']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['issued_by', 'issued_at', 'payment_reference', 'payment_submitted_at', 'verified_by', 'verified_at']);
            $table->string('status', 30)->default('unpaid')->change();
        });
    }
};
