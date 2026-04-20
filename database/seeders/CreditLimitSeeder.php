<?php

namespace Database\Seeders;

use App\Models\CreditLimit;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class CreditLimitSeeder extends Seeder
{
    /**
     * Seed credit limits based on organization type.
     * 
     * Rules:
     * - RS (hospital): Rp 20 miliar
     * - Klinik (clinic): Rp 200 juta - Rp 500 juta (random)
     * - Puskesmas: Rp 100 juta
     */
    public function run(): void
    {
        $this->command->info('Setting up credit limits for organizations...');

        $organizations = Organization::all();
        
        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Please run OrganizationSeeder first.');
            return;
        }

        // Get Super Admin as creator
        $creator = User::whereHas('roles', function ($q) {
            $q->where('name', 'Super Admin');
        })->first();

        $created = 0;
        $skipped = 0;

        foreach ($organizations as $org) {
            // Skip if credit limit already exists
            if (CreditLimit::where('organization_id', $org->id)->exists()) {
                $skipped++;
                $this->command->warn("⊘ Skipped (exists): {$org->name}");
                continue;
            }

            // Determine credit limit based on type
            $maxLimit = match ($org->type) {
                'hospital'   => 20_000_000_000, // Rp 20 miliar
                'clinic'     => rand(200_000_000, 500_000_000), // Rp 200-500 juta (random)
                'puskesmas'  => 100_000_000, // Rp 100 juta
                default      => 100_000_000, // Default Rp 100 juta
            };

            CreditLimit::create([
                'organization_id' => $org->id,
                'max_limit'       => $maxLimit,
                'is_active'       => true,
                'created_by'      => $creator?->id,
            ]);

            $created++;
            $formatted = 'Rp ' . number_format($maxLimit, 0, ',', '.');
            $this->command->info("✓ {$org->name} ({$org->type}): {$formatted}");
        }

        $this->command->newLine();
        $this->command->info("✓ Total credit limits created: {$created}");
        $this->command->info("⊘ Total skipped (exists): {$skipped}");
        $this->command->info('✓ Credit limit seeding completed!');
    }
}
