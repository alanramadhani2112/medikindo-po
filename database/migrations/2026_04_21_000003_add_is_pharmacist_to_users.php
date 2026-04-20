<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Menambahkan field is_pharmacist untuk user dengan role healthcare
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_pharmacist')->default(false)->after('is_active')
                  ->comment('Apakah user adalah apoteker (untuk role healthcare)');
            
            $table->index('is_pharmacist');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_pharmacist']);
            $table->dropColumn('is_pharmacist');
        });
    }
};
