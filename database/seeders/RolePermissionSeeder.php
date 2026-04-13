<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * RolePermissionSeeder
 *
 * Strict permission/role mapping per system spec.
 * Guard: 'web' — aligned with default session-based auth guard.
 *
 * ROLES:
 * - Super Admin        : ALL permissions
 * - Healthcare User    : view_dashboard, view_purchase_orders, create_purchase_orders, view_goods_receipt
 * - Approver           : view_dashboard, view_approvals, approve_purchase_orders
 * - Finance            : view_dashboard, view_invoices, create_invoices, view_payments, process_payments, view_credit_control
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // The guard MUST match the default web guard used for session login.
        $guard = 'web';

        // ── PERMISSIONS ───────────────────────────────────────────────────────
        $permissions = [
            // Dashboard
            'view_dashboard',
            'view_audit',

            // Purchase Orders
            'view_purchase_orders',
            'create_purchase_orders',
            'create_po',              // Alias for create_purchase_orders
            'update_purchase_orders',
            'update_po',              // Alias for update_purchase_orders
            'delete_purchase_orders',
            'submit_purchase_orders',
            'submit_po',              // Alias for submit_purchase_orders

            // Approvals
            'view_approvals',
            'approve_purchase_orders',

            // Goods Receipt
            'view_goods_receipt',
            'view_receipt',           // Alias for view_goods_receipt
            'confirm_receipt',        // For creating goods receipt

            // Invoices
            'view_invoices',
            'view_invoice',           // Alias for view_invoices
            'create_invoices',
            'manage_invoice',         // Alias for create_invoices
            'approve_invoice_discrepancy',

            // Payments
            'view_payments',
            'process_payments',
            'confirm_payment',        // For Healthcare User to confirm payment
            'verify_payment',         // For Finance to verify payment

            // Finance / Credit
            'view_credit_control',

            // Master Data
            'manage_organizations',
            'manage_suppliers',
            'manage_products',
            'manage_users',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }

        // ── ROLES ─────────────────────────────────────────────────────────────

        // Healthcare User (Hospital/Clinic staff — creates POs, receives goods)
        $healthcare = Role::firstOrCreate(['name' => 'Healthcare User', 'guard_name' => $guard]);
        $healthcare->syncPermissions([
            'view_dashboard',
            'view_purchase_orders',
            'create_purchase_orders',
            'create_po',
            'update_purchase_orders',
            'update_po',
            'submit_purchase_orders',
            'submit_po',
            'view_goods_receipt',
            'view_receipt',
            'confirm_receipt',
            'confirm_payment',        // Can confirm their own payments
        ]);

        // Approver (Medikindo Ops — approves and ships POs)
        $approver = Role::firstOrCreate(['name' => 'Approver', 'guard_name' => $guard]);
        $approver->syncPermissions([
            'view_dashboard',
            'view_purchase_orders',   // Can view PO details
            'view_approvals',
            'approve_purchase_orders',
        ]);

        // Finance (manages invoices and payments)
        $finance = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => $guard]);
        $finance->syncPermissions([
            'view_dashboard',
            'view_invoices',
            'view_invoice',
            'create_invoices',
            'manage_invoice',
            'approve_invoice_discrepancy',
            'view_payments',
            'process_payments',
            'confirm_payment',
            'verify_payment',
            'view_credit_control',
        ]);

        // Super Admin — ALL permissions
        $super = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard]);
        $super->syncPermissions(Permission::where('guard_name', $guard)->get());

        $this->command->info('✅ Roles & permissions seeded (guard: web).');
        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['Healthcare User', implode(', ', $healthcare->permissions->pluck('name')->toArray())],
                ['Approver',        implode(', ', $approver->permissions->pluck('name')->toArray())],
                ['Finance',         implode(', ', $finance->permissions->pluck('name')->toArray())],
                ['Super Admin',     'ALL (' . $super->permissions->count() . ')'],
            ]
        );
    }
}
