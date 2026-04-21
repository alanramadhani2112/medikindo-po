<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Setup default credit limits for existing organizations
        DB::statement("
            INSERT INTO credit_limits (organization_id, max_limit, is_active, created_at, updated_at)
            SELECT 
                id,
                CASE 
                    WHEN LOWER(type) = 'hospital' OR LOWER(type) = 'rs' THEN 20000000000.00
                    WHEN LOWER(type) = 'clinic' OR LOWER(type) = 'klinik' THEN 500000000.00
                    ELSE 500000000.00
                END as max_limit,
                true as is_active,
                NOW() as created_at,
                NOW() as updated_at
            FROM organizations
            WHERE id NOT IN (SELECT organization_id FROM credit_limits)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove auto-generated credit limits (keep manually created ones)
        DB::statement("
            DELETE FROM credit_limits 
            WHERE created_by IS NULL 
            AND (max_limit = 20000000000.00 OR max_limit = 500000000.00)
        ");
    }
};
