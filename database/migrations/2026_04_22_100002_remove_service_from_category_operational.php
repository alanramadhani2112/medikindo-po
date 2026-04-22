<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set any existing SERVICE values to NULL before altering enum
        DB::table('products')
            ->where('category_operational', 'SERVICE')
            ->update(['category_operational' => null]);

        // Alter enum to remove SERVICE
        DB::statement("ALTER TABLE products MODIFY COLUMN category_operational ENUM('CONSUMABLE','NON_CONSUMABLE','REAGENT','FARMASI') NULL COMMENT 'Operational classification for inventory and procurement'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE products MODIFY COLUMN category_operational ENUM('CONSUMABLE','NON_CONSUMABLE','REAGENT','FARMASI','SERVICE') NULL COMMENT 'Operational classification for inventory and procurement'");
    }
};
