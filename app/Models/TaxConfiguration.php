<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TaxConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rate',
        'is_default',
        'effective_date',
        'description',
    ];

    protected $casts = [
        'rate'           => 'decimal:2',
        'is_default'     => 'boolean',
        'effective_date' => 'date',
    ];

    // -----------------------------------------------------------------------
    // Static Lookup Methods
    // -----------------------------------------------------------------------

    /**
     * Get the active default PPN rate as a string.
     * 
     * Queries: is_default = true, effective_date <= today, ordered by effective_date DESC.
     * Falls back to '11.00' if no active record found, and logs a warning.
     */
    public static function getActivePPNRate(): string
    {
        $config = static::where('is_default', true)
            ->where('effective_date', '<=', now()->toDateString())
            ->orderByDesc('effective_date')
            ->first();

        if ($config === null) {
            Log::warning('TaxConfiguration: No active default PPN rate found. Falling back to 11.00%.');
            return '11.00';
        }

        return number_format((float) $config->rate, 2, '.', '');
    }

    /**
     * Get the e-Meterai threshold as a string (in Rupiah).
     * 
     * Queries: name = 'EMeterai_Threshold'.
     * Returns the rate field which stores the threshold value.
     */
    public static function getEMeteraiThreshold(): string
    {
        $config = static::where('name', 'EMeterai_Threshold')->first();

        if ($config === null) {
            Log::warning('TaxConfiguration: EMeterai_Threshold not found. Falling back to 5000000.');
            return '5000000.00';
        }

        return number_format((float) $config->rate, 2, '.', '');
    }
}
