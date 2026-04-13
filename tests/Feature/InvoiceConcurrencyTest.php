<?php

namespace Tests\Feature;

use App\Exceptions\ConcurrencyException;
use App\Models\CustomerInvoice;
use App\Models\Organization;
use App\Models\SupplierInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceConcurrencyTest extends TestCase
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

    public function test_supplier_invoice_has_version_column_initialized()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->assertEquals(0, $invoice->version);
    }

    public function test_supplier_invoice_increments_version_on_update()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
        ]);

        $this->assertEquals(0, $invoice->version);

        // Update the invoice
        $invoice->status = 'issued';
        $invoice->save();

        $this->assertEquals(1, $invoice->fresh()->version);
    }

    public function test_supplier_invoice_detects_concurrent_modification()
    {
        $this->expectException(ConcurrencyException::class);
        $this->expectExceptionMessage('Concurrent modification detected');
        
        // Create invoice
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
            'total_amount' => '1000.00',
        ]);

        // Simulate User A loading the invoice
        $invoiceUserA = SupplierInvoice::find($invoice->id);
        
        // Simulate User B loading and updating the invoice
        $invoiceUserB = SupplierInvoice::find($invoice->id);
        $invoiceUserB->status = 'issued';
        $invoiceUserB->save();

        // User A tries to update (should fail)
        $invoiceUserA->total_amount = '2000.00';
        $invoiceUserA->save();
    }

    public function test_supplier_invoice_allows_sequential_updates()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
        ]);

        // First update
        $invoice->status = 'issued';
        $invoice->save();
        $this->assertEquals(1, $invoice->version);

        // Second update
        $invoice = $invoice->fresh();
        $invoice->status = 'paid';
        $invoice->save();
        $this->assertEquals(2, $invoice->fresh()->version);
    }

    public function test_customer_invoice_has_version_column_initialized()
    {
        $invoice = CustomerInvoice::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->assertEquals(0, $invoice->version);
    }

    public function test_customer_invoice_increments_version_on_update()
    {
        $invoice = CustomerInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
        ]);

        $this->assertEquals(0, $invoice->version);

        // Update the invoice
        $invoice->status = 'issued';
        $invoice->save();

        $this->assertEquals(1, $invoice->fresh()->version);
    }

    public function test_customer_invoice_detects_concurrent_modification()
    {
        $this->expectException(ConcurrencyException::class);
        $this->expectExceptionMessage('Concurrent modification detected');
        
        // Create invoice
        $invoice = CustomerInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
            'total_amount' => '1000.00',
        ]);

        // Simulate User A loading the invoice
        $invoiceUserA = CustomerInvoice::find($invoice->id);
        
        // Simulate User B loading and updating the invoice
        $invoiceUserB = CustomerInvoice::find($invoice->id);
        $invoiceUserB->status = 'issued';
        $invoiceUserB->save();

        // User A tries to update (should fail)
        $invoiceUserA->total_amount = '2000.00';
        $invoiceUserA->save();
    }

    public function test_supplier_invoice_version_increments_correctly_over_multiple_updates()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
        ]);

        $this->assertEquals(0, $invoice->version);

        // Update 1
        $invoice->status = 'issued';
        $invoice->save();
        $this->assertEquals(1, $invoice->fresh()->version);

        // Update 2
        $invoice = $invoice->fresh();
        $invoice->paid_amount = '500.00';
        $invoice->save();
        $this->assertEquals(2, $invoice->fresh()->version);

        // Update 3
        $invoice = $invoice->fresh();
        $invoice->paid_amount = '1000.00';
        $invoice->save();
        $this->assertEquals(3, $invoice->fresh()->version);
    }

    public function test_supplier_invoice_does_not_increment_version_when_no_changes()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $version = $invoice->version;

        // Save without changes
        $invoice->save();

        $this->assertEquals($version, $invoice->fresh()->version);
    }

    public function test_concurrency_exception_contains_entity_information()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
        ]);

        // Simulate concurrent modification
        $invoiceUserA = SupplierInvoice::find($invoice->id);
        $invoiceUserB = SupplierInvoice::find($invoice->id);
        
        $invoiceUserB->status = 'issued';
        $invoiceUserB->save();

        try {
            $invoiceUserA->status = 'paid';
            $invoiceUserA->save();
            
            $this->fail('Expected ConcurrencyException was not thrown');
        } catch (ConcurrencyException $e) {
            $this->assertEquals('supplierinvoice', $e->getEntityType());
            $this->assertEquals($invoice->id, $e->getEntityId());
            $this->assertEquals(0, $e->getExpectedVersion());
        }
    }

    public function test_concurrency_exception_can_be_converted_to_array()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
        ]);

        // Simulate concurrent modification
        $invoiceUserA = SupplierInvoice::find($invoice->id);
        $invoiceUserB = SupplierInvoice::find($invoice->id);
        
        $invoiceUserB->status = 'issued';
        $invoiceUserB->save();

        try {
            $invoiceUserA->status = 'paid';
            $invoiceUserA->save();
            
            $this->fail('Expected ConcurrencyException was not thrown');
        } catch (ConcurrencyException $e) {
            $array = $e->toArray();
            
            $this->assertArrayHasKey('error', $array);
            $this->assertArrayHasKey('message', $array);
            $this->assertArrayHasKey('entity_type', $array);
            $this->assertArrayHasKey('entity_id', $array);
            $this->assertArrayHasKey('expected_version', $array);
            $this->assertArrayHasKey('suggestion', $array);
            
            $this->assertEquals('concurrency_conflict', $array['error']);
        }
    }

    public function test_supplier_invoice_trait_methods_work_correctly()
    {
        $invoice = SupplierInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'issued', // Start with issued status
            'paid_amount' => '0.00',
        ]);

        // Initial version should be 0
        $this->assertEquals(0, $invoice->getVersion());

        // Update mutable field (paid_amount is mutable)
        $invoice->paid_amount = '500.00';
        $invoice->save();

        // Verify in database (this is what matters for optimistic locking)
        $this->assertDatabaseHas('supplier_invoices', [
            'id' => $invoice->id,
            'version' => 1,
            'paid_amount' => '500.00',
        ]);
        
        // Fresh instance should have updated version
        $this->assertEquals(1, $invoice->fresh()->version);
    }

    public function test_customer_invoice_allows_sequential_updates()
    {
        $invoice = CustomerInvoice::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'draft',
        ]);

        // First update
        $invoice->status = 'issued';
        $invoice->save();
        $this->assertEquals(1, $invoice->version);

        // Second update
        $invoice = $invoice->fresh();
        $invoice->status = 'paid';
        $invoice->save();
        $this->assertEquals(2, $invoice->fresh()->version);
    }
}
