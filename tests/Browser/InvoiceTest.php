<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AuthenticatesUsers;

/**
 * Test: Invoice Flow (AP & AR)
 * Covers: View supplier/customer invoices, issue, authorization
 */
class InvoiceTest extends DuskTestCase
{
    use AuthenticatesUsers;

    /** @test */
    public function test_finance_can_view_supplier_invoices(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsFinance($browser);
            $browser->visit('/invoices/supplier')
                ->assertPathIs('/invoices/supplier');
        });
    }

    /** @test */
    public function test_finance_can_view_customer_invoices(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsFinance($browser);
            $browser->visit('/invoices/customer')
                ->assertPathIs('/invoices/customer');
        });
    }

    /** @test */
    public function test_healthcare_cannot_view_supplier_invoices(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);
            $browser->visit('/invoices/supplier')
                ->assertSee('403');
        });
    }

    /** @test */
    public function test_healthcare_can_view_customer_invoices(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);
            $browser->visit('/invoices/customer')
                ->assertPathIs('/invoices/customer');
        });
    }

    /** @test */
    public function test_customer_invoice_detail_shows_correct_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $invoice = \App\Models\CustomerInvoice::with('organization')->first();

            if (!$invoice) {
                $this->markTestSkipped('No customer invoice found.');
            }

            $this->loginAsSuperAdmin($browser);
            $browser->visit('/invoices/customer/' . $invoice->id)
                ->assertSee($invoice->invoice_number)
                ->assertSee($invoice->organization->name);
        });
    }

    /** @test */
    public function test_draft_customer_invoice_shows_issue_button(): void
    {
        $this->browse(function (Browser $browser) {
            $invoice = \App\Models\CustomerInvoice::where('status', 'draft')->first();

            if (!$invoice) {
                $this->markTestSkipped('No draft customer invoice found.');
            }

            $this->loginAsSuperAdmin($browser);
            $browser->visit('/invoices/customer/' . $invoice->id)
                ->assertSee('Terbitkan');
        });
    }

    /** @test */
    public function test_paid_customer_invoice_shows_paid_badge(): void
    {
        $this->browse(function (Browser $browser) {
            $invoice = \App\Models\CustomerInvoice::where('status', 'paid')->first();

            if (!$invoice) {
                $this->markTestSkipped('No paid customer invoice found.');
            }

            $this->loginAsSuperAdmin($browser);
            $browser->visit('/invoices/customer/' . $invoice->id)
                ->assertSee('Lunas');
        });
    }

    /** @test */
    public function test_ar_aging_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsFinance($browser);
            $browser->visit('/ar-aging')
                ->assertPathIs('/ar-aging');
        });
    }
}
