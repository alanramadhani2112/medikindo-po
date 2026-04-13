<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\CreditLimit;
use App\Models\CustomerInvoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $guard = 'sanctum';

            // 1. Create Organizations (Hospitals & Clinics)
            $organizations = [
                ['name' => 'Medikindo Pusat (RS)', 'code' => 'RS-001', 'type' => 'hospital'],
                ['name' => 'Klinik Sudirman', 'code' => 'CL-002', 'type' => 'clinic'],
                ['name' => 'Rumah Sakit Thamrin', 'code' => 'RS-003', 'type' => 'hospital'],
                ['name' => 'Klinik Kuningan', 'code' => 'CL-004', 'type' => 'clinic'],
            ];

            $organizationModels = [];
            foreach ($organizations as $org) {
                $organizationModels[] = Organization::firstOrCreate(['code' => $org['code']], $org);
            }

            // 2. Create Credit Limits
            foreach ($organizationModels as $org) {
                CreditLimit::updateOrCreate(
                    ['organization_id' => $org->id],
                    ['max_limit' => 1000000000, 'is_active' => true] // 1 Billion limit
                );
            }

            // 3. Create Suppliers
            $suppliers = [
                ['name' => 'Kimia Farma TD', 'code' => 'SUP-KF'],
                ['name' => 'Phapros TBK', 'code' => 'SUP-PH'],
                ['name' => 'Kalbe Farma', 'code' => 'SUP-KLB'],
                ['name' => 'Bio Farma', 'code' => 'SUP-BIO'],
            ];

            $supplierModels = [];
            foreach ($suppliers as $s) {
                $supplierModels[] = Supplier::firstOrCreate(['code' => $s['code']], $s);
            }

            // 4. Create Products
            foreach ($supplierModels as $supplier) {
                $products = [
                    ['name' => 'Paracetamol 500mg', 'sku' => $supplier->code . '-001', 'price' => 5000, 'is_narcotic' => false, 'unit' => 'Pcs', 'category' => 'Analgesic'],
                    ['name' => 'Amoxicillin 250mg', 'sku' => $supplier->code . '-002', 'price' => 12000, 'is_narcotic' => false, 'unit' => 'Pcs', 'category' => 'Antibiotic'],
                    ['name' => 'Codeine 10mg', 'sku' => $supplier->code . '-003', 'price' => 45000, 'is_narcotic' => true, 'unit' => 'Pcs', 'category' => 'Analgesic'],
                    ['name' => 'Oxycodone 5mg', 'sku' => $supplier->code . '-004', 'price' => 85000, 'is_narcotic' => true, 'unit' => 'Pcs', 'category' => 'Analgesic'],
                    ['name' => 'Insulin Glargine', 'sku' => $supplier->code . '-005', 'price' => 125000, 'is_narcotic' => false, 'unit' => 'Vial', 'category' => 'Endocrine'],
                ];

                foreach ($products as $p) {
                    Product::firstOrCreate(
                        ['sku' => $p['sku']],
                        array_merge($p, ['supplier_id' => $supplier->id, 'is_active' => true])
                    );
                }
            }

            // 5. Create POs (Active and Historic)
            $staff = User::role('Procurement Staff', $guard)->first();
            if (!$staff) {
                $staff = User::factory()->create([
                    'name' => 'Demo Procurement',
                    'email' => 'staff@medikindo.test',
                    'organization_id' => $organizationModels[0]->id,
                ]);
                $staff->assignRole('Procurement Staff');
            }

            // Create Approver user
            $approver = User::where('email', 'approver@medikindo.test')->first();
            if (!$approver) {
                $approver = User::factory()->create([
                    'name'      => 'Demo Approver',
                    'email'     => 'approver@medikindo.test',
                    'organization_id' => $organizationModels[0]->id,
                ]);
                $approver->assignRole('Approver');
            }

            // Create Organization User (Generic)
            $orgUser = User::where('email', 'klinik@test.com')->first();
            if (!$orgUser) {
                $orgUser = User::factory()->create([
                    'name'      => 'Demo User',
                    'email'     => 'klinik@test.com',
                    'organization_id' => $organizationModels[1]->id,
                ]);
                $orgUser->assignRole('Healthcare User');
            } elseif ($orgUser->organization_id === null) {
                $orgUser->update(['organization_id' => $organizationModels[1]->id]);
            }
            
            foreach ($organizationModels as $org) {
                // Historic Approved POs
                for ($i = 0; $i < 5; $i++) {
                    $supplier = $supplierModels[array_rand($supplierModels)];
                    $po = PurchaseOrder::create([
                        'po_number'    => 'PO-' . now()->subDays(60 - ($i * 5))->format('Ymd') . '-' . rand(1000, 9999),
                        'organization_id'    => $org->id,
                        'supplier_id'  => $supplier->id,
                        'created_by'   => $staff->id,
                        'status'       => PurchaseOrder::STATUS_APPROVED,
                        'total_amount' => 0,
                        'submitted_at' => now()->subDays(61 - ($i * 5)),
                        'approved_at'  => now()->subDays(60 - ($i * 5)),
                    ]);

                    $product = Product::where('supplier_id', $supplier->id)->inRandomOrder()->first();
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id'        => $product->id,
                        'quantity'          => rand(10, 100),
                        'unit_price'        => $product->price,
                        'subtotal'          => $product->price * rand(10, 100),
                    ]);
                    $po->recalculateTotals();
                    $po->save();
                }

                // Recent Pending POs
                PurchaseOrder::create([
                    'po_number'    => 'PO-' . now()->format('Ymd') . '-' . rand(1000, 9999),
                    'organization_id'    => $org->id,
                    'supplier_id'  => $supplierModels[0]->id,
                    'created_by'   => $staff->id,
                    'status'       => PurchaseOrder::STATUS_SUBMITTED,
                    'total_amount' => 2500000,
                    'submitted_at' => now()->subHours(2),
                ]);
            }

            // 6. Create Invoices (Aging data)
            foreach ($organizationModels as $org) {
                $po = PurchaseOrder::where('organization_id', $org->id)->where('status', PurchaseOrder::STATUS_APPROVED)->first();

                if ($po) {
                    // AP Invoices (due to suppliers)
                    SupplierInvoice::create([
                        'supplier_id'  => $supplierModels[0]->id,
                        'purchase_order_id' => $po->id,
                        'invoice_number' => 'INV-SUP-' . rand(1000, 9999),
                        'total_amount' => 15000000,
                        'paid_amount'  => 0,
                        'status'       => 'unpaid',
                        'due_date'     => now()->subDays(45), // Overdue 45 days
                    ]);

                    SupplierInvoice::create([
                        'supplier_id'  => $supplierModels[1]->id,
                        'purchase_order_id' => $po->id,
                        'invoice_number' => 'INV-SUP-' . rand(1000, 9999),
                        'total_amount' => 8000000,
                        'paid_amount'  => 0,
                        'status'       => 'unpaid',
                        'due_date'     => now()->addDays(15), // Future
                    ]);

                    // AR Invoices 
                    CustomerInvoice::create([
                        'organization_id'    => $org->id,
                        'purchase_order_id' => $po->id,
                        'invoice_number' => 'INV-CUST-' . rand(1000, 9999),
                        'total_amount' => 25000000,
                        'paid_amount'  => 5000000,
                        'status'       => 'partial',
                        'due_date'     => now()->subDays(10),
                    ]);
                }
            }

            // 7. Recent Payments
            foreach ($organizationModels as $org) {
                Payment::create([
                    'organization_id' => $org->id,
                    'payment_number' => 'PAY-' . rand(1000, 9999),
                    'amount'    => 5000000,
                    'type'      => 'incoming',
                    'payment_method' => 'Transfer',
                    'status'    => 'completed',
                    'payment_date' => now()->subDays(2),
                    'reference' => 'PAY-REC-' . rand(100, 999),
                ]);
            }
        });
    }
}
