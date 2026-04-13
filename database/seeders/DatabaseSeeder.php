<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CleanUserSeeder::class,
            MasterDataSeeder::class, // Organizations, Suppliers, Products
            // DemoDataSeeder::class, // Commented out - use only for demo data
        ]);
    }
}
