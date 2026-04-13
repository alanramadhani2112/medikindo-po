<?php

namespace Tests\Feature;

use App\Exceptions\ImmutabilityViolationException;
use App\Models\CustomerInvoice;
use App\Models\Organization;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    private Organization $organization;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    public function test_supplier_invoice_allows_modifications_in_draft_status()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
            'total_amount' => '1000.00',
        ]);

        // Should allow modification
        $invoice->total_amount = '2000.00';
        $invoice->save();

        $this->assertEquals('2000.00', $invoice->fresh()->total_amount);
    }

    public function test_supplier_invoice_blocks_total_amount_modification_when_issued()
    {
        $this->expectException(ImmutabilityViolationException::class);
        
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'total_amount' => '1000.00',
        ]);

        $invoice->total_amount = '2000.00';
        $invoice->save();
    }

    public function test_supplier_invoice_allows_status_change_when_issued()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'total_amount' => '1000.00',
        ]);

        // Should allow status change
        $invoice->status = 'paid';
        $invoice->save();

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_supplier_invoice_allows_paid_amount_update_when_issued()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'total_amount' => '1000.00',
            'paid_amount' => '0.00',
        ]);

        // Should allow paid_amount update
        $invoice->paid_amount = '1000.00';
        $invoice->save();

        $this->assertEquals('1000.00', $invoice->fresh()->paid_amount);
    }

    public function test_supplier_invoice_blocks_discount_amount_modification_when_issued()
    {
        $this->expectException(ImmutabilityViolationException::class);
        
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'discount_amount' => '100.00',
        ]);

        $invoice->discount_amount = '200.00';
        $invoice->save();
    }

    public function test_supplier_invoice_blocks_tax_amount_modification_when_issued()
    {
        $this->expectException(ImmutabilityViolationException::class);
        
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'tax_amount' => '110.00',
        ]);

        $invoice->tax_amount = '220.00';
        $invoice->save();
    }

    public function test_customer_invoice_allows_modifications_in_draft_status()
    {
        $invoice = CustomerInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
            'total_amount' => '1000.00',
        ]);

        // Should allow modification
        $invoice->total_amount = '2000.00';
        $invoice->save();

        $this->assertEquals('2000.00', $invoice->fresh()->total_amount);
    }

    public function test_customer_invoice_blocks_total_amount_modification_when_issued()
    {
        $this->expectException(ImmutabilityViolationException::class);
        
        $invoice = CustomerInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'total_amount' => '1000.00',
        ]);

        $invoice->total_amount = '2000.00';
        $invoice->save();
    }

    public function test_customer_invoice_allows_status_change_when_issued()
    {
        $invoice = CustomerInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'total_amount' => '1000.00',
        ]);

        // Should allow status change
        $invoice->status = 'paid';
        $invoice->save();

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_supplier_invoice_blocks_invoice_number_modification_when_issued()
    {
        $this->expectException(ImmutabilityViolationException::class);
        
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'invoice_number' => 'INV-001',
        ]);

        $invoice->invoice_number = 'INV-002';
        $invoice->save();
    }

    public function test_supplier_invoice_allows_payment_reference_update_when_issued()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'payment_reference' => null,
        ]);

        // Should allow payment_reference update
        $invoice->payment_reference = 'PAY-001';
        $invoice->save();

        $this->assertEquals('PAY-001', $invoice->fresh()->payment_reference);
    }

    public function test_supplier_invoice_blocks_multiple_immutable_fields_when_issued()
    {
        $this->expectException(ImmutabilityViolationException::class);
        
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued',
            'total_amount' => '1000.00',
            'discount_amount' => '100.00',
        ]);

        $invoice->total_amount = '2000.00';
        $invoice->discount_amount = '200.00';
        $invoice->save();
    }

    public function test_supplier_invoice_immutability_applies_to_all_immutable_statuses()
    {
        $immutableStatuses = ['issued', 'pending_approval', 'approved', 'paid', 'verified'];
        
        foreach ($immutableStatuses as $status) {
            $invoice = SupplierInvoice::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => $status,
                'total_amount' => '1000.00',
            ]);

            try {
                $invoice->total_amount = '2000.00';
                $invoice->save();
                
                $this->fail("Expected ImmutabilityViolationException for status: {$status}");
            } catch (ImmutabilityViolationException $e) {
                $this->assertStringContainsString('total_amount', $e->getMessage());
            }
        }
    }

    public function test_customer_invoice_immutability_applies_to_all_immutable_statuses()
    {
        $immutableStatuses = ['issued', 'pending_approval', 'approved', 'paid', 'verified'];
        
        foreach ($immutableStatuses as $status) {
            $invoice = CustomerInvoice::factory()->create([
                'organization_id' => $this->organization->id,
                'status' => $status,
                'total_amount' => '1000.00',
            ]);

            try {
                $invoice->total_amount = '2000.00';
                $invoice->save();
                
                $this->fail("Expected ImmutabilityViolationException for status: {$status}");
            } catch (ImmutabilityViolationException $e) {
                $this->assertStringContainsString('total_amount', $e->getMessage());
            }
        }
    }
}
