<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AuthenticatesUsers;

/**
 * Test: Role-Based Authorization
 * Covers: Each role can/cannot access specific pages
 */
class AuthorizationTest extends DuskTestCase
{
    use AuthenticatesUsers;

    /**
     * Pages that Finance CAN access
     * @test
     */
    public function test_finance_can_access_their_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsFinance($browser);

            $pages = [
                '/dashboard',
                '/invoices/supplier',
                '/invoices/customer',
                '/payment-proofs',
                '/payments',
                '/ar-aging',
            ];

            foreach ($pages as $page) {
                $browser->visit($page)
                    ->assertPathIs($page);
            }
        });
    }

    /**
     * Pages that Healthcare User CANNOT access
     * @test
     */
    public function test_healthcare_blocked_from_finance_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);

            $blockedPages = [
                '/invoices/supplier',   // Internal Medikindo data
                '/approvals',           // Approver only
            ];

            foreach ($blockedPages as $page) {
                $browser->visit($page)
                    ->assertSee('403');
            }
        });
    }

    /**
     * Pages that Healthcare User CAN access
     * @test
     */
    public function test_healthcare_can_access_their_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);

            $pages = [
                '/dashboard',
                '/purchase-orders',
                '/purchase-orders/create',
                '/goods-receipts',
                '/payment-proofs',
                '/payment-proofs/create',
                '/invoices/customer',
            ];

            foreach ($pages as $page) {
                $browser->visit($page)
                    ->assertPathIs($page);
            }
        });
    }

    /**
     * Super Admin can access everything
     * @test
     */
    public function test_super_admin_can_access_all_pages(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSuperAdmin($browser);

            $pages = [
                '/dashboard',
                '/purchase-orders',
                '/approvals',
                '/goods-receipts',
                '/invoices/supplier',
                '/invoices/customer',
                '/payment-proofs',
                '/payments',
                '/ar-aging',
                '/financial-controls',
                '/products',
                '/suppliers',
                '/organizations',
                '/users',
                '/bank-accounts',
            ];

            foreach ($pages as $page) {
                $browser->visit($page)
                    ->assertPathIs($page);
            }
        });
    }

    /**
     * Approver can only access approvals + view POs
     * @test
     */
    public function test_approver_access_scope(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsApprover($browser);

            // Can access
            $browser->visit('/approvals')
                ->assertPathIs('/approvals');

            $browser->visit('/purchase-orders')
                ->assertPathIs('/purchase-orders');

            // Cannot create POs
            $browser->visit('/purchase-orders/create')
                ->assertSee('403');
        });
    }
}
