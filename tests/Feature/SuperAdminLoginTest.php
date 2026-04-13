<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_super_admin_can_login_with_correct_credentials()
    {
        // Create Super Admin user
        $admin = User::create([
            'name' => 'Alan Ramadhani',
            'email' => 'alanramadhani21@gmail.com',
            'password' => Hash::make('Medikindo@2026!'),
            'organization_id' => null,
            'is_active' => true,
        ]);

        $admin->assignRole('Super Admin');

        // Attempt login
        $response = $this->post(route('login'), [
            'email' => 'alanramadhani21@gmail.com',
            'password' => 'Medikindo@2026!',
        ]);

        // Should redirect to dashboard
        $response->assertRedirect(route('web.dashboard'));

        // User should be authenticated
        $this->assertAuthenticatedAs($admin);

        // User should have Super Admin role
        $this->assertTrue($admin->hasRole('Super Admin'));
    }

    public function test_super_admin_cannot_login_with_wrong_password()
    {
        // Create Super Admin user
        $admin = User::create([
            'name' => 'Alan Ramadhani',
            'email' => 'alanramadhani21@gmail.com',
            'password' => Hash::make('Medikindo@2026!'),
            'organization_id' => null,
            'is_active' => true,
        ]);

        $admin->assignRole('Super Admin');

        // Attempt login with wrong password
        $response = $this->post(route('login'), [
            'email' => 'alanramadhani21@gmail.com',
            'password' => 'WrongPassword123!',
        ]);

        // Should redirect back with error
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Email atau password tidak valid.');

        // User should NOT be authenticated
        $this->assertGuest();
    }

    public function test_inactive_super_admin_cannot_login()
    {
        // Create inactive Super Admin user
        $admin = User::create([
            'name' => 'Alan Ramadhani',
            'email' => 'alanramadhani21@gmail.com',
            'password' => Hash::make('Medikindo@2026!'),
            'organization_id' => null,
            'is_active' => false, // Inactive
        ]);

        $admin->assignRole('Super Admin');

        // Attempt login
        $response = $this->post(route('login'), [
            'email' => 'alanramadhani21@gmail.com',
            'password' => 'Medikindo@2026!',
        ]);

        // Should redirect back with error
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator.');

        // User should NOT be authenticated
        $this->assertGuest();
    }

    public function test_super_admin_has_all_permissions()
    {
        // Create Super Admin user
        $admin = User::create([
            'name' => 'Alan Ramadhani',
            'email' => 'alanramadhani21@gmail.com',
            'password' => Hash::make('Medikindo@2026!'),
            'organization_id' => null,
            'is_active' => true,
        ]);

        $admin->assignRole('Super Admin');

        // Check critical permissions
        $this->assertTrue($admin->can('view_dashboard'));
        $this->assertTrue($admin->can('view_purchase_orders'));
        $this->assertTrue($admin->can('create_purchase_orders'));
        $this->assertTrue($admin->can('view_approvals'));
        $this->assertTrue($admin->can('approve_purchase_orders'));
        $this->assertTrue($admin->can('view_invoices'));
        $this->assertTrue($admin->can('create_invoices'));
        $this->assertTrue($admin->can('view_payments'));
        $this->assertTrue($admin->can('process_payments'));
        $this->assertTrue($admin->can('view_credit_control'));
        $this->assertTrue($admin->can('manage_organizations'));
        $this->assertTrue($admin->can('manage_suppliers'));
        $this->assertTrue($admin->can('manage_products'));
        $this->assertTrue($admin->can('manage_users'));
    }

    public function test_super_admin_can_access_dashboard_after_login()
    {
        // Create Super Admin user
        $admin = User::create([
            'name' => 'Alan Ramadhani',
            'email' => 'alanramadhani21@gmail.com',
            'password' => Hash::make('Medikindo@2026!'),
            'organization_id' => null,
            'is_active' => true,
        ]);

        $admin->assignRole('Super Admin');

        // Login
        $this->actingAs($admin);

        // Access dashboard
        $response = $this->get(route('web.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }
}
