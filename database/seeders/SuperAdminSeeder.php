<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'alanramadhani21@gmail.com'],
            [
                'name'      => 'Alan Ramadhani',
                'password'  => Hash::make('Medikindo@2026!'),
                'organization_id' => null,
                'is_active' => true,
            ],
        );

        $admin->syncRoles(['Super Admin']);

        $this->command->info("✅ Super Admin: {$admin->email}");
        $this->command->info("   Password  : Medikindo@2026!");
        $this->command->warn("   ⚠️  Segera ganti password setelah login pertama!");
    }
}
