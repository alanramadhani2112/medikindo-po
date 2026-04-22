<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('category_regulatory', ['OBAT', 'ALKES', 'PKRT', 'KOSMETIK', 'SUPLEMEN'])
                  ->nullable()
                  ->after('category')
                  ->comment('Regulatory classification: OBAT, ALKES, PKRT, KOSMETIK, SUPLEMEN');

            $table->enum('category_class', [
                    // OBAT classes
                    'OBAT_KERAS', 'OBAT_BEBAS', 'OBAT_BEBAS_TERBATAS',
                    'NARKOTIKA', 'PSIKOTROPIKA', 'BIOLOGIS',
                    // ALKES classes
                    'KELAS_A', 'KELAS_B', 'KELAS_C', 'KELAS_D',
                ])
                  ->nullable()
                  ->after('category_regulatory')
                  ->comment('Class within regulatory category. NULL if not OBAT or ALKES');

            $table->enum('category_operational', ['CONSUMABLE', 'NON_CONSUMABLE', 'REAGENT', 'FARMASI', 'SERVICE'])
                  ->nullable()
                  ->after('category_class')
                  ->comment('Operational classification for inventory and procurement');

            $table->index('category_regulatory');
            $table->index('category_operational');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['category_regulatory']);
            $table->dropIndex(['category_operational']);
            $table->dropColumn(['category_regulatory', 'category_class', 'category_operational']);
        });
    }
};
