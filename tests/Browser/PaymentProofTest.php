<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Browser\Traits\AuthenticatesUsers;

/**
 * Test: Payment Proof State Machine
 * Covers: Submit, Verify, Approve, Reject, Resubmit, Recall
 */
class PaymentProofTest extends DuskTestCase
{
    use AuthenticatesUsers;

    /** @test */
    public function test_healthcare_can_view_payment_proofs(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);
            $browser->visit('/payment-proofs')
                ->assertPathIs('/payment-proofs');
        });
    }

    /** @test */
    public function test_healthcare_can_access_submit_form(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsHealthcare($browser);
            $browser->visit('/payment-proofs/create')
                ->assertPathIs('/payment-proofs/create');
        });
    }

    /** @test */
    public function test_finance_can_view_all_payment_proofs(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsFinance($browser);
            $browser->visit('/payment-proofs')
                ->assertPathIs('/payment-proofs');
        });
    }

    /** @test */
    public function test_finance_can_access_verify_page_for_submitted_proof(): void
    {
        $this->browse(function (Browser $browser) {
            // Find a submitted payment proof
            $proof = \App\Models\PaymentProof::where('status', 'submitted')->first();

            if (!$proof) {
                $this->markTestSkipped('No submitted payment proof found in database.');
            }

            $this->loginAsFinance($browser);
            $browser->visit('/payment-proofs/' . $proof->id . '/verify')
                ->assertPathIs('/payment-proofs/' . $proof->id . '/verify');
        });
    }

    /** @test */
    public function test_healthcare_cannot_verify_payment_proof(): void
    {
        $this->browse(function (Browser $browser) {
            $proof = \App\Models\PaymentProof::where('status', 'submitted')->first();

            if (!$proof) {
                $this->markTestSkipped('No submitted payment proof found.');
            }

            $this->loginAsHealthcare($browser);
            $browser->visit('/payment-proofs/' . $proof->id . '/verify')
                ->assertSee('403');
        });
    }

    /** @test */
    public function test_rejected_proof_shows_resubmit_button(): void
    {
        $this->browse(function (Browser $browser) {
            $proof = \App\Models\PaymentProof::where('status', 'rejected')->first();

            if (!$proof) {
                $this->markTestSkipped('No rejected payment proof found.');
            }

            // Login as the submitter
            $submitter = $proof->submittedBy;
            $this->loginAs($browser, $submitter->email);

            $browser->visit('/payment-proofs/' . $proof->id)
                ->assertSee('Ajukan Ulang Bukti Pembayaran');
        });
    }

    /** @test */
    public function test_resubmit_form_requires_minimum_10_words_in_notes(): void
    {
        $this->browse(function (Browser $browser) {
            $proof = \App\Models\PaymentProof::where('status', 'rejected')->first();

            if (!$proof) {
                $this->markTestSkipped('No rejected payment proof found.');
            }

            $submitter = $proof->submittedBy;
            $this->loginAs($browser, $submitter->email);

            $browser->visit('/payment-proofs/' . $proof->id . '/resubmit')
                ->waitFor('textarea[name="resubmission_notes"]')
                ->type('resubmission_notes', 'Terlalu sedikit kata')
                // Button should be disabled (less than 10 words)
                ->assertButtonDisabled('Ajukan Ulang');
        });
    }

    /** @test */
    public function test_resubmit_button_enabled_with_10_words(): void
    {
        $this->browse(function (Browser $browser) {
            $proof = \App\Models\PaymentProof::where('status', 'rejected')->first();

            if (!$proof) {
                $this->markTestSkipped('No rejected payment proof found.');
            }

            $submitter = $proof->submittedBy;
            $this->loginAs($browser, $submitter->email);

            $browser->visit('/payment-proofs/' . $proof->id . '/resubmit')
                ->waitFor('textarea[name="resubmission_notes"]')
                ->select('payment_method', 'Bank Transfer')
                ->type('resubmission_notes', 'Saya sudah memperbaiki data transfer dan mengunggah bukti baru yang lebih jelas')
                // Button should now be enabled (10+ words)
                ->assertButtonEnabled('Ajukan Ulang');
        });
    }

    /** @test */
    public function test_approved_proof_shows_no_verify_button(): void
    {
        $this->browse(function (Browser $browser) {
            $proof = \App\Models\PaymentProof::where('status', 'approved')->first();

            if (!$proof) {
                $this->markTestSkipped('No approved payment proof found.');
            }

            $this->loginAsFinance($browser);
            $browser->visit('/payment-proofs/' . $proof->id)
                ->assertDontSee('Verifikasi Pembayaran');
        });
    }

    /** @test */
    public function test_resubmitted_proof_shows_verify_button_for_finance(): void
    {
        $this->browse(function (Browser $browser) {
            $proof = \App\Models\PaymentProof::where('status', 'resubmitted')->first();

            if (!$proof) {
                $this->markTestSkipped('No resubmitted payment proof found.');
            }

            $this->loginAsFinance($browser);
            $browser->visit('/payment-proofs/' . $proof->id)
                ->assertSee('Verifikasi Pembayaran');
        });
    }
}
