<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\TaxConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class TaxConfigurationTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // TAX CONFIGURATION MODEL TESTS
    // -----------------------------------------------------------------------

    public function test_can_create_tax_configuration(): void
    {
        $taxConfig = TaxConfiguration::factory()->create([
            'name' => 'PPN 2024',
            'rate' => 11.00,
            'is_default' => true,
            'effective_date' => now()->subDays(30),
            'description' => 'PPN rate for 2024',
        ]);

        $this->assertDatabaseHas('tax_configurations', [
            'name' => 'PPN 2024',
            'rate' => 11.00,
            'is_default' => true,
            'description' => 'PPN rate for 2024',
        ]);

        $this->assertEquals(11.00, $taxConfig->rate);
        $this->assertTrue($taxConfig->is_default);
    }

    public function test_tax_configuration_casts(): void
    {
        $taxConfig = TaxConfiguration::factory()->create([
            'rate' => '12.50',
            'is_default' => '1',
            'effective_date' => '2024-01-01',
        ]);

        $this->assertIsString($taxConfig->rate); // Decimal cast returns string
        $this->assertEquals('12.50', $taxConfig->rate);
        $this->assertIsBool($taxConfig->is_default);
        $this->assertInstanceOf(\Carbon\Carbon::class, $taxConfig->effective_date);
    }

    // -----------------------------------------------------------------------
    // ACTIVE PPN RATE TESTS
    // -----------------------------------------------------------------------

    public function test_get_active_ppn_rate_returns_latest_default(): void
    {
        // Create multiple PPN rates with different effective dates
        TaxConfiguration::factory()->create([
            'name' => 'PPN 2023',
            'rate' => 10.00,
            'is_default' => true,
            'effective_date' => now()->subDays(100),
        ]);

        TaxConfiguration::factory()->create([
            'name' => 'PPN 2024',
            'rate' => 11.00,
            'is_default' => true,
            'effective_date' => now()->subDays(30),
        ]);

        TaxConfiguration::factory()->create([
            'name' => 'PPN Future',
            'rate' => 12.00,
            'is_default' => true,
            'effective_date' => now()->addDays(30), // Future date, should be ignored
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.00', $activePPNRate);
    }

    public function test_get_active_ppn_rate_ignores_non_default(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'PPN Default',
            'rate' => 11.00,
            'is_default' => true,
            'effective_date' => now()->subDays(30),
        ]);

        TaxConfiguration::factory()->create([
            'name' => 'PPN Non-Default',
            'rate' => 15.00,
            'is_default' => false, // Not default
            'effective_date' => now()->subDays(10),
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.00', $activePPNRate);
    }

    public function test_get_active_ppn_rate_ignores_future_dates(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'PPN Current',
            'rate' => 11.00,
            'is_default' => true,
            'effective_date' => now()->subDays(30),
        ]);

        TaxConfiguration::factory()->create([
            'name' => 'PPN Future',
            'rate' => 12.00,
            'is_default' => true,
            'effective_date' => now()->addDays(10), // Future date
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.00', $activePPNRate);
    }

    public function test_get_active_ppn_rate_fallback_when_no_config(): void
    {
        // No tax configurations exist
        Log::shouldReceive('warning')
            ->once()
            ->with('TaxConfiguration: No active default PPN rate found. Falling back to 11.00%.');

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.00', $activePPNRate);
    }

    public function test_get_active_ppn_rate_orders_by_effective_date_desc(): void
    {
        // Create configs in random order
        TaxConfiguration::factory()->create([
            'name' => 'PPN Old',
            'rate' => 10.00,
            'is_default' => true,
            'effective_date' => now()->subDays(100),
        ]);

        TaxConfiguration::factory()->create([
            'name' => 'PPN Latest',
            'rate' => 11.50,
            'is_default' => true,
            'effective_date' => now()->subDays(5), // Most recent
        ]);

        TaxConfiguration::factory()->create([
            'name' => 'PPN Middle',
            'rate' => 11.00,
            'is_default' => true,
            'effective_date' => now()->subDays(50),
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.50', $activePPNRate); // Most recent effective date
    }

    // -----------------------------------------------------------------------
    // E-METERAI THRESHOLD TESTS
    // -----------------------------------------------------------------------

    public function test_get_emeterai_threshold_returns_configured_value(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'EMeterai_Threshold',
            'rate' => 5000000.00, // 5 million
            'is_default' => false,
            'effective_date' => now(),
            'description' => 'E-Meterai threshold amount',
        ]);

        $threshold = TaxConfiguration::getEMeteraiThreshold();

        $this->assertEquals('5000000.00', $threshold);
    }

    public function test_get_emeterai_threshold_fallback_when_not_found(): void
    {
        // No EMeterai_Threshold configuration exists
        Log::shouldReceive('warning')
            ->once()
            ->with('TaxConfiguration: EMeterai_Threshold not found. Falling back to 5000000.');

        $threshold = TaxConfiguration::getEMeteraiThreshold();

        $this->assertEquals('5000000.00', $threshold);
    }

    public function test_get_emeterai_threshold_with_different_amount(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'EMeterai_Threshold',
            'rate' => 10000000.00, // 10 million
            'is_default' => false,
            'effective_date' => now(),
        ]);

        $threshold = TaxConfiguration::getEMeteraiThreshold();

        $this->assertEquals('10000000.00', $threshold);
    }

    // -----------------------------------------------------------------------
    // DECIMAL PRECISION TESTS
    // -----------------------------------------------------------------------

    public function test_ppn_rate_decimal_precision(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'PPN Precise',
            'rate' => 11.75, // Precise decimal
            'is_default' => true,
            'effective_date' => now()->subDays(10),
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.75', $activePPNRate);
    }

    public function test_emeterai_threshold_decimal_precision(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'EMeterai_Threshold',
            'rate' => 5500000.50, // With cents
            'is_default' => false,
            'effective_date' => now(),
        ]);

        $threshold = TaxConfiguration::getEMeteraiThreshold();

        $this->assertEquals('5500000.50', $threshold);
    }

    // -----------------------------------------------------------------------
    // BUSINESS SCENARIO TESTS
    // -----------------------------------------------------------------------

    public function test_ppn_rate_change_scenario(): void
    {
        // Initial PPN rate
        TaxConfiguration::factory()->create([
            'name' => 'PPN 2023',
            'rate' => 10.00,
            'is_default' => true,
            'effective_date' => now()->subDays(365),
        ]);

        // Rate change effective from specific date
        TaxConfiguration::factory()->create([
            'name' => 'PPN 2024',
            'rate' => 11.00,
            'is_default' => true,
            'effective_date' => now()->subDays(30),
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.00', $activePPNRate);
    }

    public function test_multiple_tax_types_scenario(): void
    {
        // PPN (default tax)
        TaxConfiguration::factory()->create([
            'name' => 'PPN Standard',
            'rate' => 11.00,
            'is_default' => true,
            'effective_date' => now()->subDays(30),
        ]);

        // PPh (non-default tax)
        TaxConfiguration::factory()->create([
            'name' => 'PPh 23',
            'rate' => 2.00,
            'is_default' => false,
            'effective_date' => now()->subDays(30),
        ]);

        // E-Meterai threshold
        TaxConfiguration::factory()->create([
            'name' => 'EMeterai_Threshold',
            'rate' => 5000000.00,
            'is_default' => false,
            'effective_date' => now(),
        ]);

        $ppnRate = TaxConfiguration::getActivePPNRate();
        $emeteraiThreshold = TaxConfiguration::getEMeteraiThreshold();

        $this->assertEquals('11.00', $ppnRate);
        $this->assertEquals('5000000.00', $emeteraiThreshold);

        // Verify PPh is not returned as default PPN
        $this->assertNotEquals('2.00', $ppnRate);
    }

    public function test_effective_date_boundary_conditions(): void
    {
        $today = now()->startOfDay();

        // Effective exactly today
        TaxConfiguration::factory()->create([
            'name' => 'PPN Today',
            'rate' => 11.00,
            'is_default' => true,
            'effective_date' => $today,
        ]);

        // Effective tomorrow (should be ignored)
        TaxConfiguration::factory()->create([
            'name' => 'PPN Tomorrow',
            'rate' => 12.00,
            'is_default' => true,
            'effective_date' => $today->copy()->addDay(),
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.00', $activePPNRate);
    }

    // -----------------------------------------------------------------------
    // ERROR HANDLING AND EDGE CASES
    // -----------------------------------------------------------------------

    public function test_zero_rate_handling(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'Zero PPN',
            'rate' => 0.00,
            'is_default' => true,
            'effective_date' => now()->subDays(10),
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('0.00', $activePPNRate);
    }

    public function test_high_precision_rate(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'High Precision PPN',
            'rate' => 11.123456,
            'is_default' => true,
            'effective_date' => now()->subDays(10),
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('11.12', $activePPNRate); // Should be formatted to 2 decimal places
    }

    public function test_negative_rate_handling(): void
    {
        TaxConfiguration::factory()->create([
            'name' => 'Negative Rate',
            'rate' => -5.00,
            'is_default' => true,
            'effective_date' => now()->subDays(10),
        ]);

        $activePPNRate = TaxConfiguration::getActivePPNRate();

        $this->assertEquals('-5.00', $activePPNRate); // Should handle negative rates
    }
}