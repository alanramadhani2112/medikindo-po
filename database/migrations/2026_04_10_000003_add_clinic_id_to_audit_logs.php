<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add organization_id to audit_logs table.
     * (Originally named add_clinic_id but uses organization_id directly.)
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('audit_logs', 'organization_id')) {
                $blueprint->foreignId('organization_id')->nullable()->after('user_id')->constrained('organizations')->nullOnDelete();
                $blueprint->index('organization_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['organization_id']);
            $blueprint->dropColumn('organization_id');
        });
    }
};
