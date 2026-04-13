<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('credit_limits', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('max_limit');
        });
    }

    public function down(): void
    {
        Schema::table('credit_limits', function (Blueprint $table) {
            $table->dropColumn(['max_limit', 'is_active']);
        });
    }
};
