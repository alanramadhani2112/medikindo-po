<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique()->comment('Short clinic code, e.g. RSU-001');
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('license_number', 100)->nullable()->comment('Operating license');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // Now safe to add FK: users.clinic_id → clinics.id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('clinic_id')->references('id')->on('clinics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
        });

        Schema::dropIfExists('clinics');
    }
};
