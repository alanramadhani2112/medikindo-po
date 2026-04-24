<?php

namespace Database\Factories;

use App\Models\TaxConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxConfiguration>
 */
class TaxConfigurationFactory extends Factory
{
    protected $model = TaxConfiguration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $taxTypes = [
            ['name' => 'PPN Standard', 'rate' => 11.00, 'is_default' => true],
            ['name' => 'PPN Reduced', 'rate' => 5.00, 'is_default' => false],
            ['name' => 'PPh 21', 'rate' => 5.00, 'is_default' => false],
            ['name' => 'PPh 23', 'rate' => 2.00, 'is_default' => false],
            ['name' => 'PPh 26', 'rate' => 20.00, 'is_default' => false],
            ['name' => 'EMeterai_Threshold', 'rate' => 5000000.00, 'is_default' => false],
        ];

        $taxType = $this->faker->randomElement($taxTypes);

        return [
            'name' => $taxType['name'],
            'rate' => $taxType['rate'],
            'is_default' => $taxType['is_default'],
            'effective_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'description' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Create a default PPN tax configuration.
     */
    public function ppnDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'PPN Standard',
            'rate' => 11.00,
            'is_default' => true,
            'description' => 'Standard PPN rate',
        ]);
    }

    /**
     * Create a non-default tax configuration.
     */
    public function nonDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => false,
        ]);
    }

    /**
     * Create an e-Meterai threshold configuration.
     */
    public function emeteraiThreshold(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'EMeterai_Threshold',
            'rate' => 5000000.00,
            'is_default' => false,
            'description' => 'E-Meterai threshold amount',
        ]);
    }

    /**
     * Create a tax configuration with specific rate.
     */
    public function withRate(float $rate): static
    {
        return $this->state(fn (array $attributes) => [
            'rate' => $rate,
        ]);
    }

    /**
     * Create a tax configuration effective from specific date.
     */
    public function effectiveFrom(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_date' => $date,
        ]);
    }

    /**
     * Create a PPh 23 tax configuration.
     */
    public function pph23(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'PPh 23',
            'rate' => 2.00,
            'is_default' => false,
            'description' => 'PPh 23 withholding tax',
        ]);
    }

    /**
     * Create a custom tax configuration with specific name and rate.
     */
    public function custom(string $name, float $rate, bool $isDefault = false): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'rate' => $rate,
            'is_default' => $isDefault,
        ]);
    }
}