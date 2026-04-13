<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique()->comment('Supplier/distributor code');
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('npwp', 30)->nullable()->comment('Tax ID');
            $table->string('license_number', 100)->nullable()->comment('Distribution license');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
