<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Test: Authentication Flow
 * Setiap test menggunakan browser instance terpisah via multiple browse() calls
 */
class LoginTest extends DuskTestCase
{
    /** @test */
    public function test_login_page_loads(): void
    {
        $this->browse(function (Browser $b1) {
            $b1->visit('/login')
                ->assertSee('Login System')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
        });
    }

    /** @test */
    public function test_super_admin_can_login(): void
    {
        $this->browse(function (Browser $b1) {
            $b1->visit('/login')
                ->waitFor('input[name="email"]', 5)
                ->type('email', 'alanramadhani21@gmail.com')
                ->type('password', 'password123')
                ->press('button[type="submit"]')
                ->waitForLocation('/dashboard', 15)
                ->assertPathIs('/dashboard');
        });
    }

    /** @test */
    public function test_healthcare_user_can_login(): void
    {
        // Use separate browser instance to avoid shared session
        $this->browse(function (Browser $b1, Browser $b2) {
            $b2->visit('/login')
                ->waitFor('input[name="email"]', 5)
                ->type('email', 'budi.santoso@testhospital.com')
                ->type('password', 'password123')
                ->press('button[type="submit"]')
                ->waitForLocation('/dashboard', 15)
                ->assertPathIs('/dashboard');
        });
    }

    /** @test */
    public function test_finance_user_can_login(): void
    {
        $this->browse(function (Browser $b1, Browser $b2) {
            $b2->visit('/login')
                ->waitFor('input[name="email"]', 5)
                ->type('email', 'ahmad.hidayat@medikindo.com')
                ->type('password', 'password123')
                ->press('button[type="submit"]')
                ->waitForLocation('/dashboard', 15)
                ->assertPathIs('/dashboard');
        });
    }

    /** @test */
    public function test_invalid_credentials_show_error(): void
    {
        $this->browse(function (Browser $b1, Browser $b2) {
            $b2->visit('/login')
                ->waitFor('input[name="email"]', 5)
                ->type('email', 'wrong@email.com')
                ->type('password', 'wrongpassword')
                ->press('button[type="submit"]')
                ->waitFor('.alert-danger', 5)
                ->assertSee('Email atau password tidak valid');
        });
    }

    /** @test */
    public function test_authenticated_user_redirected_from_login(): void
    {
        $user = User::role('Super Admin')->first();

        $this->browse(function (Browser $b1) use ($user) {
            $b1->loginAs($user)
                ->visit('/login')
                ->assertPathIs('/dashboard');
        });
    }

    /** @test */
    public function test_unauthenticated_redirected_to_login(): void
    {
        $this->browse(function (Browser $b1, Browser $b2) {
            // b2 is a fresh browser with no session
            $b2->visit('/dashboard')
                ->assertPathIs('/login');
        });
    }
}
