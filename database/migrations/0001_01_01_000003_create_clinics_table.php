<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the 'organizations' table directly (previously 'clinics').
     * The rename happened historically; for fresh migrations we create organizations directly.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique()->comment('Short organization code, e.g. RSU-001');
            $table->string('type', 50)->default('clinic')->comment('hospital, clinic, puskesmas');
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('license_number', 100)->nullable()->comment('Operating license');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });

        // Now safe to add FK: users.organization_id → organizations.id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        Schema::dropIfExists('organizations');
    }
};
