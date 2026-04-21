<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            // Expand the status enum to include 'resubmitted'
            // Note: MySQL ALTER ENUM — must list ALL values
            $table->enum('status', ['submitted', 'verified', 'approved', 'rejected', 'recalled', 'resubmitted'])
                  ->default('submitted')
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            // Remove 'resubmitted' from enum
            $table->enum('status', ['submitted', 'verified', 'approved', 'rejected', 'recalled'])
                  ->default('submitted')
                  ->change();
        });
    }
};
