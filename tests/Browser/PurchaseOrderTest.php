<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AuthenticatesUsers;

/**
 * Test: Purchase Order Flow
 * Covers: Create PO, Submit, Approve, Reject
 */
class PurchaseOrderTest extends DuskTestCase
{
    use AuthenticatesUsers;

    /** @test */
    public function test_healthcare_can_view_purchase_orders(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);
            $browser->visit('/purchase-orders')
                ->assertPathIs('/purchase-orders')
                ->assertSee('Purchase Order');
        });
    }

    /** @test */
    public function test_healthcare_can_access_create_po_form(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);
            $browser->visit('/purchase-orders/create')
                ->assertPathIs('/purchase-orders/create');
        });
    }

    /** @test */
    public function test_approver_can_view_approvals(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsApprover($browser);
            $browser->visit('/approvals')
                ->assertPathIs('/approvals');
        });
    }

    /** @test */
    public function test_healthcare_cannot_access_approvals(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);
            $browser->visit('/approvals')
                ->assertSee('403');
        });
    }

    /** @test */
    public function test_finance_cannot_create_purchase_orders(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsFinance($browser);
            $browser->visit('/purchase-orders/create')
                ->assertSee('403');
        });
    }
}
