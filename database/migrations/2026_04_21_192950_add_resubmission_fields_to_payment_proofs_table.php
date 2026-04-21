<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            // Link to the rejected proof this is a resubmission of
            if (!Schema::hasColumn('payment_proofs', 'resubmission_of_id')) {
                $table->foreignId('resubmission_of_id')
                    ->nullable()
                    ->after('correction_of_id')
                    ->constrained('payment_proofs')
                    ->nullOnDelete();
            }

            // Notes explaining what was fixed in the resubmission
            if (!Schema::hasColumn('payment_proofs', 'resubmission_notes')) {
                $table->text('resubmission_notes')->nullable()->after('resubmission_of_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropForeign(['resubmission_of_id']);
            $table->dropColumn(['resubmission_of_id', 'resubmission_notes']);
        });
    }
};
