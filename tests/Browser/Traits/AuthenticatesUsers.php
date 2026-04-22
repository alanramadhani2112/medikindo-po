<?php

namespace Tests\Browser\Traits;

use App\Models\User;
use Laravel\Dusk\Browser;

trait AuthenticatesUsers
{
    /**
     * Login as Super Admin
     */
    protected function loginAsSuperAdmin(Browser $browser): void
    {
        $this->loginAs($browser, 'alanramadhani21@gmail.com');
    }

    /**
     * Login as Healthcare User
     */
    protected function loginAsHealthcare(Browser $browser): void
    {
        $this->loginAs($browser, 'budi.santoso@testhospital.com');
    }

    /**
     * Login as Approver
     */
    protected function loginAsApprover(Browser $browser): void
    {
        $this->loginAs($browser, 'siti.nurhaliza@medikindo.com');
    }

    /**
     * Login as Finance
     */
    protected function loginAsFinance(Browser $browser): void
    {
        $this->loginAs($browser, 'ahmad.hidayat@medikindo.com');
    }

    /**
     * Generic login helper — uses Dusk's built-in loginAs() to bypass form
     * Use b2/b3 for fresh browser instances to avoid shared session
     */
    protected function loginAs(Browser $browser, string $email, string $password = 'password123'): void
    {
        $user = \App\Models\User::where('email', $email)->firstOrFail();
        $browser->loginAs($user)->visit('/dashboard');
    }

    /**
     * Logout
     */
    protected function logout(Browser $browser): void
    {
        $browser->visit('/logout')
            ->waitForLocation('/login');
    }
}
