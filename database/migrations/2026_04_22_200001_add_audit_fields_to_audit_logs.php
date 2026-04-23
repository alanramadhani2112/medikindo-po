<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('module', 50)->nullable()->after('action')
                  ->comment('System module: PO, GR, INVOICE, PAYMENT, MASTER_DATA, AUTH');
            $table->json('before_value')->nullable()->after('metadata')
                  ->comment('State of the entity before the action');
            $table->json('after_value')->nullable()->after('before_value')
                  ->comment('State of the entity after the action');
            $table->string('correlation_id', 36)->nullable()->after('after_value')
                  ->comment('UUID to correlate related audit events in a single flow');

            $table->index('module');
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['module']);
            $table->dropIndex(['correlation_id']);
            $table->dropColumn(['module', 'before_value', 'after_value', 'correlation_id']);
        });
    }
};
