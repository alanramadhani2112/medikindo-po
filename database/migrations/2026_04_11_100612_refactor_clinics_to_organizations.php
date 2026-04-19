<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration originally renamed 'clinics' → 'organizations' and updated all FKs.
     * Since the base migrations now create 'organizations' directly with 'organization_id' columns,
     * this migration is a no-op on fresh installs. It is kept for historical reference only.
     */
    public function up(): void
    {
        // No-op: base migrations already use 'organizations' and 'organization_id' directly.
        // All FK renames (clinic_id → organization_id) are handled at the source migrations.
    }

    public function down(): void
    {
        // No-op
    }
};
