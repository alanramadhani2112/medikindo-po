<?php

namespace Tests\Feature;

use App\Enums\CustomerInvoiceStatus;
use App\Models\CustomerInvoice;
use App\Models\Organization;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ARAgingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    // -----------------------------------------------------------------------
    // AR AGING DASHBOARD ACCESS TESTS
    // -----------------------------------------------------------------------

    public function test_can_access_ar_aging_dashboard(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertSee('AR Aging');
        $response->assertSee('Klasifikasi piutang berdasarkan umur tagihan');
        $response->assertSee('Belum Jatuh Tempo');
        $response->assertSee('1–30 Hari');
        $response->assertSee('31–60 Hari');
        $response->assertSee('61–90 Hari');
        $response->assertSee('>90 Hari');
    }

    public function test_healthcare_user_can_access_ar_aging_dashboard(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsHealthcareUser($organization);

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertSee('AR Aging');
        $response->assertSee('Klasifikasi piutang berdasarkan umur tagihan');
    }

    public function test_super_admin_can_access_ar_aging_dashboard(): void
    {
        $user = $this->actingAsSuperAdmin();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertSee('AR Aging');
    }

    // -----------------------------------------------------------------------
    // AGING BUCKET CALCULATION TESTS
    // -----------------------------------------------------------------------

    public function test_aging_bucket_current_not_overdue(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Invoice with due date in the future (not overdue)
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->addDays(5),
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $this->assertEquals('current', $invoice->aging_bucket);
        $this->assertEquals(0, $invoice->days_overdue);
    }

    public function test_aging_bucket_1_30_days_overdue(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Invoice overdue by 15 days
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $this->assertEquals('1-30', $invoice->aging_bucket);
        $this->assertEquals(15, $invoice->days_overdue);
    }

    public function test_aging_bucket_31_60_days_overdue(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Invoice overdue by 45 days
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::PARTIAL_PAID,
                'due_date' => now()->subDays(45),
                'total_amount' => 800000,
                'paid_amount' => 200000
            ])
            ->create();

        $this->assertEquals('31-60', $invoice->aging_bucket);
        $this->assertEquals(45, $invoice->days_overdue);
    }

    public function test_aging_bucket_61_90_days_overdue(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Invoice overdue by 75 days
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(75),
                'total_amount' => 1200000,
                'paid_amount' => 0
            ])
            ->create();

        $this->assertEquals('61-90', $invoice->aging_bucket);
        $this->assertEquals(75, $invoice->days_overdue);
    }

    public function test_aging_bucket_90_plus_days_overdue(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Invoice overdue by 120 days
        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(120),
                'total_amount' => 2000000,
                'paid_amount' => 0
            ])
            ->create();

        $this->assertEquals('90+', $invoice->aging_bucket);
        $this->assertEquals(120, $invoice->days_overdue);
    }

    public function test_paid_invoices_excluded_from_aging(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Paid invoice should not appear in aging
        $paidInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::PAID,
                'due_date' => now()->subDays(30),
                'total_amount' => 1000000,
                'paid_amount' => 1000000
            ])
            ->create();

        // Void invoice should not appear in aging
        $voidInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::VOID,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertDontSee($paidInvoice->invoice_number);
        $response->assertDontSee($voidInvoice->invoice_number);
    }

    // -----------------------------------------------------------------------
    // DASHBOARD DISPLAY TESTS
    // -----------------------------------------------------------------------

    public function test_ar_aging_dashboard_displays_correct_buckets(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create invoices in different aging buckets
        $currentInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->addDays(5),
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $overdue30Invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $overdue60Invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::PARTIAL_PAID,
                'due_date' => now()->subDays(45),
                'total_amount' => 800000,
                'paid_amount' => 200000
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        
        // Check that invoices appear in correct sections
        $response->assertSee($currentInvoice->invoice_number);
        $response->assertSee($overdue30Invoice->invoice_number);
        $response->assertSee($overdue60Invoice->invoice_number);
        
        // Check total amounts
        $response->assertSee('Rp 1.000.000'); // Current bucket
        $response->assertSee('Rp 500.000');   // 1-30 bucket
        $response->assertSee('Rp 600.000');   // 31-60 bucket (outstanding amount)
    }

    public function test_ar_aging_dashboard_shows_empty_state(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertSee('Tidak Ada Piutang Outstanding');
        $response->assertSee('Semua invoice sudah lunas atau belum ada invoice yang aktif saat ini');
    }

    public function test_ar_aging_dashboard_calculates_totals_correctly(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create multiple invoices
        CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->addDays(5),
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::PARTIAL_PAID,
                'due_date' => now()->subDays(45),
                'total_amount' => 800000,
                'paid_amount' => 200000
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        
        // Total outstanding should be 1,000,000 + 500,000 + 600,000 = 2,100,000
        $response->assertSee('Rp 2.100.000');
    }

    // -----------------------------------------------------------------------
    // FILTERING AND SEARCH TESTS
    // -----------------------------------------------------------------------

    public function test_can_filter_by_aging_bucket(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create invoices in different buckets
        $currentInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->addDays(5),
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $overdueInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        // Filter by current bucket
        $response = $this->get('/ar-aging?bucket=current');

        $response->assertStatus(200);
        $response->assertSee($currentInvoice->invoice_number);
        $response->assertDontSee($overdueInvoice->invoice_number);

        // Filter by 1-30 bucket
        $response = $this->get('/ar-aging?bucket=1-30');

        $response->assertStatus(200);
        $response->assertSee($overdueInvoice->invoice_number);
        $response->assertDontSee($currentInvoice->invoice_number);
    }

    public function test_can_search_by_invoice_number(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $invoice1 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'invoice_number' => 'INV-2026-001',
                'due_date' => now()->addDays(5),
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $invoice2 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'invoice_number' => 'INV-2026-002',
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging?search=INV-2026-001');

        $response->assertStatus(200);
        $response->assertSee($invoice1->invoice_number);
        $response->assertDontSee($invoice2->invoice_number);
    }

    public function test_can_search_by_organization_name(): void
    {
        $org1 = Organization::factory()->state(['name' => 'RS Harapan Kita'])->create();
        $org2 = Organization::factory()->state(['name' => 'Klinik Sehat Sentosa'])->create();
        $user = $this->actingAsSuperAdmin(); // Super admin can see all organizations

        $invoice1 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $org1->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->addDays(5),
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $invoice2 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $org2->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging?search=Harapan');

        $response->assertStatus(200);
        $response->assertSee($invoice1->invoice_number);
        $response->assertSee('RS Harapan Kita');
        $response->assertDontSee($invoice2->invoice_number);
    }

    // -----------------------------------------------------------------------
    // MULTI-TENANT SECURITY TESTS
    // -----------------------------------------------------------------------

    public function test_ar_aging_multi_tenant_isolation(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $user = $this->actingAsFinanceUser($org1);

        $invoice1 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $org1->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->addDays(5),
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $invoice2 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $org2->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertSee($invoice1->invoice_number);
        $response->assertDontSee($invoice2->invoice_number);
    }

    public function test_super_admin_can_see_all_organizations_ar_aging(): void
    {
        $org1 = Organization::factory()->create();
        $org2 = Organization::factory()->create();
        
        $user = $this->actingAsSuperAdmin();

        $invoice1 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $org1->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->addDays(5),
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $invoice2 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $org2->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertSee($invoice1->invoice_number);
        $response->assertSee($invoice2->invoice_number);
    }

    // -----------------------------------------------------------------------
    // OUTSTANDING AMOUNT CALCULATION TESTS
    // -----------------------------------------------------------------------

    public function test_outstanding_amount_calculation(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Full outstanding
        $invoice1 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        $this->assertEquals(1000000, $invoice1->outstanding_amount);

        // Partial payment
        $invoice2 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::PARTIAL_PAID,
                'total_amount' => 800000,
                'paid_amount' => 300000
            ])
            ->create();

        $this->assertEquals(500000, $invoice2->outstanding_amount);

        // Fully paid
        $invoice3 = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::PAID,
                'total_amount' => 600000,
                'paid_amount' => 600000
            ])
            ->create();

        $this->assertEquals(0, $invoice3->outstanding_amount);
    }

    // -----------------------------------------------------------------------
    // BUSINESS LOGIC TESTS
    // -----------------------------------------------------------------------

    public function test_ar_aging_excludes_draft_invoices(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Draft invoice should not appear in AR aging
        $draftInvoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::DRAFT,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertDontSee($draftInvoice->invoice_number);
    }

    public function test_ar_aging_handles_null_due_dates(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Invoice without due date should not appear in AR aging
        $invoiceNoDueDate = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => null,
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        $response->assertDontSee($invoiceNoDueDate->invoice_number);
    }

    // -----------------------------------------------------------------------
    // PERFORMANCE TESTS
    // -----------------------------------------------------------------------

    public function test_ar_aging_performance_with_many_invoices(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create 50 invoices across different aging buckets
        for ($i = 0; $i < 50; $i++) {
            CustomerInvoice::factory()
                ->state([
                    'organization_id' => $organization->id,
                    'status' => CustomerInvoiceStatus::ISSUED,
                    'due_date' => now()->subDays(rand(0, 120)),
                    'total_amount' => rand(100000, 2000000),
                    'paid_amount' => 0
                ])
                ->create();
        }

        $startTime = microtime(true);
        
        $response = $this->get('/ar-aging');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        
        // Should complete within 5 seconds (adjusted for test environment)
        $this->assertLessThan(5.0, $executionTime, 'AR Aging dashboard should load within 5 seconds');
    }

    // -----------------------------------------------------------------------
    // UI/UX TESTS
    // -----------------------------------------------------------------------

    public function test_ar_aging_displays_correct_badge_colors(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        // Create invoices in different buckets
        CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->addDays(5), // Current
                'total_amount' => 1000000,
                'paid_amount' => 0
            ])
            ->create();

        CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15), // 1-30 days
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        
        // Check for color indicators in the response
        $response->assertSee('text-success'); // Current bucket (green)
        $response->assertSee('text-warning'); // 1-30 bucket (yellow)
    }

    public function test_ar_aging_action_buttons_work(): void
    {
        $organization = Organization::factory()->create();
        $user = $this->actingAsFinanceUser($organization);

        $invoice = CustomerInvoice::factory()
            ->state([
                'organization_id' => $organization->id,
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(15),
                'total_amount' => 500000,
                'paid_amount' => 0
            ])
            ->create();

        $response = $this->get('/ar-aging');

        $response->assertStatus(200);
        
        // Check for action buttons
        $response->assertSee('Lihat Detail');
        $response->assertSee('Bayar Sekarang');
        
        // Check that links are properly formed
        $response->assertSee(route('web.invoices.customer.show', $invoice));
        $response->assertSee(route('web.payments.create.incoming', ['invoice_id' => $invoice->id]));
    }
}