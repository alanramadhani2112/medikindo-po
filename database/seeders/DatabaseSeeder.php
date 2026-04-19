<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Core access control and users
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,

            // Core master data
            OrganizationSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,

            // Product normalization/update
            UpdateProductCategoriesSeeder::class,
            UpdateProductPricesSeeder::class,

            // Fiscal config
            TaxConfigurationSeeder::class, // AR Invoice: PPN rates and e-Meterai threshold

            // Clean role-based test users (run after roles and organizations exist)
            CleanUserSeeder::class,

            // Maintenance seeder (safe; no-op on fresh DB with no submitted PO)
            FixMissingApprovals::class,

            // Optional additional data
            // MasterDataSeeder::class, // Alternative wrapper for Organization/Supplier/Product
            // ExtendedProductSeeder::class, // Add extended product catalog
            // DemoDataSeeder::class, // Commented out - use only for demo data
        ]);
    }
}
