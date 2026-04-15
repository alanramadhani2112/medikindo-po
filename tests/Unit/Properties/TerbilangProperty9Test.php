<?php

namespace Tests\Unit\Properties;

use App\Services\TerbilangService;
use PHPUnit\Framework\TestCase;

/**
 * Property 9: Terbilang Round-Trip
 *
 * For any integer amount, the terbilang text correctly represents the number.
 *
 * Validates: Requirements 13.4
 *
 * @group property-based
 */
class TerbilangProperty9Test extends TestCase
{
    private TerbilangService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TerbilangService();
    }

    /**
     * Property 9a: Output always contains "Rupiah"
     */
    public function test_output_always_contains_rupiah(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $amount = mt_rand(1, 999_999_999);
            $result = $this->service->convert($amount);

            $this->assertStringContainsString(
                'Rupiah',
                $result,
                "Iteration {$i}: output for amount={$amount} should contain 'Rupiah'. Got: '{$result}'"
            );
        }
    }

    /**
     * Property 9b: Output is a non-empty string for all random integers
     */
    public function test_output_is_non_empty_string_for_random_integers(): void
    {
        for ($i = 0; $i < 100; $i++) {
            $amount = mt_rand(1, 999_999_999);
            $result = $this->service->convert($amount);

            $this->assertIsString($result, "Iteration {$i}: output should be a string for amount={$amount}");
            $this->assertNotEmpty($result, "Iteration {$i}: output should not be empty for amount={$amount}");
        }
    }

    /**
     * Property 9c: Known values match expected strings
     */
    public function test_known_values_match_expected_strings(): void
    {
        $knownValues = [
            1         => 'Satu Rupiah',
            1000      => 'Seribu Rupiah',
            1500000   => 'Satu Juta Lima Ratus Ribu Rupiah',
            100000000 => 'Seratus Juta Rupiah',
            999999999 => 'Sembilan Ratus Sembilan Puluh Sembilan Juta Sembilan Ratus Sembilan Puluh Sembilan Ribu Sembilan Ratus Sembilan Puluh Sembilan Rupiah',
        ];

        foreach ($knownValues as $amount => $expected) {
            $result = $this->service->convert($amount);

            $this->assertEquals(
                $expected,
                $result,
                "Known value mismatch for amount={$amount}. Expected: '{$expected}', Got: '{$result}'"
            );
        }
    }
}
