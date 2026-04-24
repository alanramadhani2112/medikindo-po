<?php

namespace Tests;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $guard = config('auth.defaults.guard', 'web');

        // 1. Create Permissions for both guards
        $permissions = [
            'create_po','update_po','submit_po','view_po',
            'approve_po','reject_po','view_approvals','approve_purchase_orders',
            'confirm_receipt','view_receipt','view_goods_receipt',
            'view_invoice','manage_invoice',
            'confirm_payment','verify_payment','view_payments','process_payments',
            'manage_product','manage_supplier','manage_organization','manage_user',
            'view_audit','full_access'
        ];
        
        foreach ($permissions as $p) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => $guard]);
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $p, 'guard_name' => 'sanctum']);
        }

        // 2. Create Roles & Assign Permissions
        $superAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard]);
        $superAdmin->syncPermissions($permissions); // All permissions

        $adminPusat = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => $guard]);
        $adminPusat->syncPermissions($permissions); // All permissions like Super Admin

        $healthcareUser = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Healthcare User', 'guard_name' => $guard]);
        $healthcareUser->syncPermissions([
            'create_po','update_po','submit_po','view_po',
            'confirm_receipt','view_receipt','view_goods_receipt',
            'view_invoice', // Removed 'confirm_payment' - Healthcare users should NOT process payments
            'manage_product','manage_supplier','manage_user',
            'view_audit'
        ]);

        $procStaff = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Procurement Staff', 'guard_name' => $guard]);
        $procStaff->syncPermissions(['create_po','update_po','submit_po','view_po']);

        $approver = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Approver', 'guard_name' => $guard]);
        $approver->syncPermissions(['view_po','approve_po','reject_po','view_approvals','approve_purchase_orders']);

        $finance = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Finance', 'guard_name' => $guard]);
        $finance->syncPermissions(['view_invoice','manage_invoice','confirm_payment','verify_payment','view_payments','process_payments','view_goods_receipt']);
        
        // Create roles for sanctum guard (API)
        $superAdminSanctum = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'sanctum']);
        $superAdminSanctum->syncPermissions($permissions);
        
        $adminPusatSanctum = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => 'sanctum']);
        $adminPusatSanctum->syncPermissions($permissions);
        
        $healthcareUserSanctum = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Healthcare User', 'guard_name' => 'sanctum']);
        $healthcareUserSanctum->syncPermissions([
            'create_po','update_po','submit_po','view_po',
            'confirm_receipt','view_receipt','view_goods_receipt',
            'view_invoice', // Removed 'confirm_payment' - Healthcare users should NOT process payments
            'manage_product','manage_supplier','manage_user',
            'view_audit'
        ]);
        
        $procStaffSanctum = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Procurement Staff', 'guard_name' => 'sanctum']);
        $procStaffSanctum->syncPermissions(['create_po','update_po','submit_po','view_po']);
        
        $approverSanctum = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Approver', 'guard_name' => 'sanctum']);
        $approverSanctum->syncPermissions(['view_po','approve_po','reject_po','view_approvals','approve_purchase_orders']);
        
        $financeSanctum = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Finance', 'guard_name' => 'sanctum']);
        $financeSanctum->syncPermissions(['view_invoice','manage_invoice','confirm_payment','verify_payment','view_payments','process_payments','view_goods_receipt']);
    }

    // -----------------------------------------------------------------------
    // Auth Helpers
    // -----------------------------------------------------------------------

    protected function actingAsSuperAdmin(?Organization $organization = null): User
    {
        $user = User::factory()->superAdmin()->create(['organization_id' => $organization?->id]);
        Sanctum::actingAs($user);
        return $user;
    }

    protected function actingAsHealthcareUser(Organization $organization): User
    {
        $user = User::factory()->healthcareUser()->forOrganization($organization)->create();
        Sanctum::actingAs($user);
        return $user;
    }

    protected function actingAsApprover(?Organization $organization = null): User
    {
        $user = User::factory()->approver()->create(['organization_id' => $organization?->id]);
        Sanctum::actingAs($user);
        return $user;
    }

    protected function actingAsFinanceUser(?Organization $organization = null): User
    {
        $user = User::factory()->financeUser()->create(['organization_id' => $organization?->id]);
        Sanctum::actingAs($user);
        return $user;
    }

    protected function actingAsUser(User $user): User
    {
        Sanctum::actingAs($user);
        return $user;
    }

    // -----------------------------------------------------------------------
    // User Creation Helpers
    // -----------------------------------------------------------------------

    protected function createHealthcareUser(Organization $organization): User
    {
        return User::factory()->healthcareUser()->forOrganization($organization)->create();
    }

    protected function createApprover(?Organization $organization = null): User
    {
        return User::factory()->approver()->create(['organization_id' => $organization?->id]);
    }

    protected function createFinanceUser(?Organization $organization = null): User
    {
        return User::factory()->financeUser()->create(['organization_id' => $organization?->id]);
    }

    protected function createSuperAdmin(?Organization $organization = null): User
    {
        return User::factory()->superAdmin()->create(['organization_id' => $organization?->id]);
    }
}
