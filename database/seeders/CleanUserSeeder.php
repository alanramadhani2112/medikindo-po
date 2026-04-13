<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CleanUserSeeder extends Seeder
{
    /**
     * Seed clean user data - 1 user per role
     */
    public function run(): void
    {
        $this->command->info('🗑️  Dropping all existing users...');
        
        // Delete all users
        User::query()->delete();
        
        $this->command->info('✅ All users deleted');
        $this->command->newLine();

        // Get or create test organization for non-admin users
        $organization = Organization::firstOrCreate(
            ['code' => 'TEST-ORG'],
            [
                'name' => 'Test Hospital',
                'address' => 'Jl. Test No. 123, Jakarta',
                'phone' => '021-12345678',
                'email' => 'info@testhospital.com',
                'is_active' => true,
            ]
        );

        $this->command->info('📋 Creating 1 user per role...');
        $this->command->newLine();

        // ===================================================================
        // 1. SUPER ADMIN
        // ===================================================================
        $superAdmin = User::create([
            'name' => 'Alan Ramadhani',
            'email' => 'alanramadhani21@gmail.com',
            'password' => Hash::make('Medikindo@2026!'),
            'organization_id' => null, // Super Admin tidak terikat organisasi
            'is_active' => true,
        ]);
        $superAdmin->assignRole('Super Admin');

        $this->command->info('✅ Super Admin created:');
        $this->command->line("   📧 Email   : {$superAdmin->email}");
        $this->command->line("   🔑 Password: Medikindo@2026!");
        $this->command->line("   🏢 Org     : NULL (All Access)");
        $this->command->newLine();

        // ===================================================================
        // 2. HEALTHCARE USER
        // ===================================================================
        $healthcareUser = User::create([
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi.santoso@testhospital.com',
            'password' => Hash::make('Healthcare@2026!'),
            'organization_id' => $organization->id,
            'is_active' => true,
        ]);
        $healthcareUser->assignRole('Healthcare User');

        $this->command->info('✅ Healthcare User created:');
        $this->command->line("   📧 Email   : {$healthcareUser->email}");
        $this->command->line("   🔑 Password: Healthcare@2026!");
        $this->command->line("   🏢 Org     : {$organization->name}");
        $this->command->line("   📋 Role    : Healthcare User");
        $this->command->newLine();

        // ===================================================================
        // 3. APPROVER
        // ===================================================================
        $approver = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@medikindo.com',
            'password' => Hash::make('Approver@2026!'),
            'organization_id' => null, // Approver bisa approve semua organisasi
            'is_active' => true,
        ]);
        $approver->assignRole('Approver');

        $this->command->info('✅ Approver created:');
        $this->command->line("   📧 Email   : {$approver->email}");
        $this->command->line("   🔑 Password: Approver@2026!");
        $this->command->line("   🏢 Org     : NULL (All Organizations)");
        $this->command->line("   📋 Role    : Approver");
        $this->command->newLine();

        // ===================================================================
        // 4. FINANCE
        // ===================================================================
        $finance = User::create([
            'name' => 'Ahmad Hidayat',
            'email' => 'ahmad.hidayat@medikindo.com',
            'password' => Hash::make('Finance@2026!'),
            'organization_id' => null, // Finance bisa manage semua organisasi
            'is_active' => true,
        ]);
        $finance->assignRole('Finance');

        $this->command->info('✅ Finance created:');
        $this->command->line("   📧 Email   : {$finance->email}");
        $this->command->line("   🔑 Password: Finance@2026!");
        $this->command->line("   🏢 Org     : NULL (All Organizations)");
        $this->command->line("   📋 Role    : Finance");
        $this->command->newLine();

        // ===================================================================
        // SUMMARY
        // ===================================================================
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('✅ CLEAN USER SEEDING COMPLETE');
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->newLine();

        $this->command->table(
            ['Role', 'Name', 'Email', 'Password', 'Organization'],
            [
                [
                    'Super Admin',
                    $superAdmin->name,
                    $superAdmin->email,
                    'Medikindo@2026!',
                    'NULL (All Access)',
                ],
                [
                    'Healthcare User',
                    $healthcareUser->name,
                    $healthcareUser->email,
                    'Healthcare@2026!',
                    $organization->name,
                ],
                [
                    'Approver',
                    $approver->name,
                    $approver->email,
                    'Approver@2026!',
                    'NULL (All Orgs)',
                ],
                [
                    'Finance',
                    $finance->name,
                    $finance->email,
                    'Finance@2026!',
                    'NULL (All Orgs)',
                ],
            ]
        );

        $this->command->newLine();
        $this->command->warn('⚠️  IMPORTANT: Change all passwords after first login!');
        $this->command->newLine();
    }
}
