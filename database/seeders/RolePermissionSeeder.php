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
 * - Admin Pusat        : Full operational access (PO, GR, Invoice, Payment, Approval) - NO master data
 * - Healthcare User    : Create PO, receive goods (GR), view invoices, confirm payment
 * - Approver           : View PO, approve/reject PO
 * - Finance            : Create invoices, process payments, view GR/PO for context
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

            // Credit Notes
            'view_credit_notes',
            'create_credit_notes',
            'issue_credit_notes',
            'apply_credit_notes',

            // Payments
            'view_payments',
            'process_payments',
            'confirm_payment',        // For Healthcare User to confirm payment
            'verify_payment',         // For Finance to verify payment

            // Payment Proof System
            'submit_payment_proof',
            'upload_payment_document',
            'view_payment_status',
            'update_payment_submission',
            'verify_payment_proof',
            'approve_payment',
            'view_payment_documents',
            'request_payment_clarification',

            // Finance / Credit
            'view_credit_control',

            // Inventory
            'view_inventory',
            'manage_inventory',

            // Reports
            'view_reports',

            // Master Data
            'manage_organizations',
            'manage_suppliers',
            'manage_products',
            'manage_users',
            'manage_bank_accounts',
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
            'confirm_receipt',          // Can create GR (receive goods)
            'view_invoices',            // Can view invoices related to their PO
            'confirm_payment',          // Can confirm their own payments
            'view_inventory',           // Can view inventory
            // Payment Proof System - Healthcare User permissions
            'submit_payment_proof',
            'upload_payment_document',
            'view_payment_status',
            'update_payment_submission',
        ]);

        // Approver (Medikindo Ops — approves and ships POs)
        $approver = Role::firstOrCreate(['name' => 'Approver', 'guard_name' => $guard]);
        $approver->syncPermissions([
            'view_dashboard',
            'view_purchase_orders',   // Can view PO details
            'view_approvals',
            'approve_purchase_orders',
            'view_goods_receipt',     // Can view GR to verify delivery was received
            'view_receipt',           // Alias
        ]);

        // Admin Pusat (Medikindo Central Admin — manages operations: PO, GR, Invoice, Payment)
        $adminPusat = Role::firstOrCreate(['name' => 'Admin Pusat', 'guard_name' => $guard]);
        $adminPusat->syncPermissions([
            'view_dashboard',
            'view_audit',
            // PO
            'view_purchase_orders',
            'create_purchase_orders',
            'create_po',
            'update_purchase_orders',
            'update_po',
            'submit_purchase_orders',
            'submit_po',
            // Approval
            'view_approvals',
            'approve_purchase_orders',
            // GR
            'view_goods_receipt',
            'view_receipt',
            'confirm_receipt',
            // Invoice
            'view_invoices',
            'view_invoice',
            'create_invoices',
            'manage_invoice',
            'approve_invoice_discrepancy',
            // Payment
            'view_payments',
            'process_payments',
            'confirm_payment',
            'verify_payment',
            // Payment Proof System - Admin Pusat has all permissions
            'submit_payment_proof',
            'upload_payment_document',
            'view_payment_status',
            'update_payment_submission',
            'verify_payment_proof',
            'approve_payment',
            'view_payment_documents',
            'request_payment_clarification',
            // Finance
            'view_credit_control',
            // Inventory
            'view_inventory',
            'manage_inventory',
            // Reports
            'view_reports',
        ]);

        // Finance (manages invoices and payments)
        $finance = Role::firstOrCreate(['name' => 'Finance', 'guard_name' => $guard]);
        $finance->syncPermissions([
            'view_dashboard',
            'view_purchase_orders',     // Need to view PO for context
            'view_goods_receipt',       // Need to view GR to create invoice
            'view_receipt',
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
            'view_inventory',           // Can view inventory
            'view_reports',             // Can view reports
            // Payment Proof System - Finance User permissions
            'verify_payment_proof',
            'approve_payment',
            'view_payment_documents',
            'request_payment_clarification',
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
                ['Admin Pusat',     implode(', ', $adminPusat->permissions->pluck('name')->toArray())],
                ['Finance',         implode(', ', $finance->permissions->pluck('name')->toArray())],
                ['Super Admin',     'ALL (' . $super->permissions->count() . ')'],
            ]
        );
    }
}
