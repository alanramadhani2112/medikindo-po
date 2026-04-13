<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates all master data in the correct order:
     * 1. Organizations (hospitals, clinics, puskesmas)
     * 2. Suppliers (pharmaceutical companies)
     * 3. Products (medicines, medical supplies)
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════════════════════╗');
        $this->command->info('║         SEEDING MASTER DATA - MEDIKINDO PO             ║');
        $this->command->info('╚════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Step 1: Organizations
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('STEP 1: Creating Organizations');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->call(OrganizationSeeder::class);

        $this->command->info('');

        // Step 2: Suppliers
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('STEP 2: Creating Suppliers');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->call(SupplierSeeder::class);

        $this->command->info('');

        // Step 3: Products
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('STEP 3: Creating Products');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->call(ProductSeeder::class);

        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════════════════════╗');
        $this->command->info('║              MASTER DATA SEEDING COMPLETE!             ║');
        $this->command->info('╚════════════════════════════════════════════════════════╝');
        $this->command->info('');

        // Summary
        $this->showSummary();
    }

    /**
     * Show summary of created data
     */
    private function showSummary(): void
    {
        $organizations = \App\Models\Organization::count();
        $suppliers = \App\Models\Supplier::count();
        $products = \App\Models\Product::count();

        $this->command->info('📊 SUMMARY:');
        $this->command->info('   • Organizations: ' . $organizations);
        $this->command->info('   • Suppliers: ' . $suppliers);
        $this->command->info('   • Products: ' . $products);
        $this->command->info('');
        $this->command->info('✓ All master data has been seeded successfully!');
        $this->command->info('');
        $this->command->info('Next steps:');
        $this->command->info('  1. Login as Healthcare User');
        $this->command->info('  2. Create a Purchase Order');
        $this->command->info('  3. Select a supplier to see products');
        $this->command->info('');
    }
}
