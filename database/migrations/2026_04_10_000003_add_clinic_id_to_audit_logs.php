<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $blueprint) {
            $blueprint->foreignId('clinic_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $blueprint->index('clinic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['clinic_id']);
            $blueprint->dropColumn('clinic_id');
        });
    }
};
