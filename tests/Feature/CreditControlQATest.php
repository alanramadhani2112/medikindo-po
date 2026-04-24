<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Organization;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\CreditLimit;
use App\Models\CreditUsage;
use App\Models\CustomerInvoice;
use App\Models\SupplierInvoice;
use App\Services\CreditControlService;
use App\Enums\CustomerInvoiceStatus;
use App\Enums\SupplierInvoiceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreditControlQATest extends TestCase
{
    use RefreshDatabase;

    private CreditControlService $creditControlService;
    private Organization $organization;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->organization = Organization::factory()->create();
        $this->user = $this->actingAsHealthcareUser($this->organization);
        $this->creditControlService = app(CreditControlService::class);
        
        // Clean up any existing credit limits for test organization
        CreditLimit::where('organization_id', $this->organization->id)->delete();
    }

    public function test_allows_po_creation_when_no_credit_limit_configured(): void
    {
        $result = $this->creditControlService->canCreatePO($this->organization->id, 50000);

        $this->assertTrue($result['allowed']);
        $this->assertNull($result['reason']);
        $this->assertEquals('PO dapat dibuat.', $result['message']);
        $this->assertEmpty($result['details']);
    }

    public function test_allows_po_creation_within_credit_limit(): void
    {
        CreditLimit::factory()
            ->forOrganization($this->organization)
            ->withLimit(100000)
            ->create();

        $result = $this->creditControlService->canCreatePO($this->organization->id, 50000);

        $this->assertTrue($result['allowed']);
        $this->assertNull($result['reason']);
        $this->assertEquals('PO dapat dibuat.', $result['message']);
    }

    public function test_blocks_po_creation_when_credit_limit_exceeded(): void
    {
        CreditLimit::factory()
            ->forOrganization($this->organization)
            ->withLimit(100000)
            ->create();

        $result = $this->creditControlService->canCreatePO($this->organization->id, 150000);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('credit_limit_exceeded', $result['reason']);
        $this->assertEquals('Tidak dapat membuat PO. Limit kredit akan terlampaui.', $result['message']);
        $this->assertEquals(100000, $result['details']['credit_limit']);
        $this->assertEquals(150000, $result['details']['requested_amount']);
        $this->assertEquals(50000, $result['details']['shortfall']);
    }

    public function test_blocks_po_creation_when_overdue_supplier_invoices_exist(): void
    {
        SupplierInvoice::factory()
            ->forOrganization($this->organization)
            ->create([
                'status' => SupplierInvoiceStatus::OVERDUE,
                'due_date' => now()->subDays(5),
            ]);

        $result = $this->creditControlService->canCreatePO($this->organization->id, 50000);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('overdue_invoices', $result['reason']);
        $this->assertEquals('Tidak dapat membuat PO. Terdapat invoice yang sudah jatuh tempo.', $result['message']);
        $this->assertEquals(1, $result['details']['overdue_count']);
    }

    public function test_blocks_po_creation_when_overdue_customer_invoices_exist(): void
    {
        CustomerInvoice::factory()
            ->forOrganization($this->organization)
            ->create([
                'status' => CustomerInvoiceStatus::ISSUED,
                'due_date' => now()->subDays(10),
                'total_amount' => 30000,
                'paid_amount' => 0,
            ]);

        $result = $this->creditControlService->canCreatePO($this->organization->id, 50000);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('overdue_invoices', $result['reason']);
        $this->assertStringContainsString('jatuh tempo', $result['message']);
    }

    public function test_reserves_credit_when_po_is_submitted(): void
    {
        CreditLimit::factory()
            ->forOrganization($this->organization)
            ->withLimit(100000)
            ->create();

        $po = PurchaseOrder::factory()
            ->forOrganization($this->organization)
            ->create(['total_amount' => 50000]);

        $this->creditControlService->reserveCredit($po);

        $this->assertDatabaseHas('credit_usages', [
            'organization_id' => $this->organization->id,
            'purchase_order_id' => $po->id,
            'amount_used' => 50000,
            'status' => 'reserved',
        ]);
    }

    public function test_throws_exception_when_reserving_credit_exceeds_limit(): void
    {
        CreditLimit::factory()
            ->forOrganization($this->organization)
            ->withLimit(100000)
            ->create();

        $po = PurchaseOrder::factory()
            ->forOrganization($this->organization)
            ->create(['total_amount' => 150000]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Tidak dapat membuat PO. Limit kredit akan terlampaui.');

        $this->creditControlService->reserveCredit($po);
    }

    public function test_bills_credit_when_po_is_approved(): void
    {
        $po = PurchaseOrder::factory()
            ->forOrganization($this->organization)
            ->create(['total_amount' => 50000]);

        CreditUsage::factory()
            ->forPurchaseOrder($po)
            ->reserved()
            ->create();

        $this->creditControlService->billCredit($po);

        $this->assertDatabaseHas('credit_usages', [
            'purchase_order_id' => $po->id,
            'status' => 'billed',
        ]);
    }

    public function test_reverses_credit_when_po_is_rejected(): void
    {
        $po = PurchaseOrder::factory()
            ->forOrganization($this->organization)
            ->create(['total_amount' => 50000]);

        CreditUsage::factory()
            ->forPurchaseOrder($po)
            ->reserved()
            ->create();

        $this->creditControlService->reverseCredit($po);

        $this->assertDatabaseMissing('credit_usages', [
            'purchase_order_id' => $po->id,
            'status' => 'reserved',
        ]);
    }

    public function test_releases_credit_when_payment_is_received(): void
    {
        $po = PurchaseOrder::factory()
            ->forOrganization($this->organization)
            ->create(['total_amount' => 50000]);

        CreditUsage::factory()
            ->forPurchaseOrder($po)
            ->billed()
            ->create();

        $this->creditControlService->releaseCreditByAmount($this->organization->id, $po, 50000);

        $this->assertDatabaseHas('credit_usages', [
            'purchase_order_id' => $po->id,
            'status' => 'released',
        ]);
    }

    public function test_calculates_current_outstanding_correctly(): void
    {
        // Create AR invoices with specific amounts
        CustomerInvoice::factory()
            ->forOrganization($this->organization)
            ->create([
                'status' => CustomerInvoiceStatus::ISSUED,
                'total_amount' => 30000,
                'paid_amount' => 0, // outstanding = 30000
            ]);

        CustomerInvoice::factory()
            ->forOrganization($this->organization)
            ->create([
                'status' => CustomerInvoiceStatus::PARTIAL_PAID,
                'total_amount' => 50000,
                'paid_amount' => 30000, // outstanding = 20000
            ]);

        // Create reserved credit
        CreditUsage::factory()
            ->forOrganization($this->organization)
            ->reserved()
            ->withAmount(15000)
            ->create();

        // Create billed credit
        CreditUsage::factory()
            ->forOrganization($this->organization)
            ->billed()
            ->withAmount(10000)
            ->create();

        $outstanding = $this->creditControlService->getCurrentOutstanding($this->organization->id);

        // 30000 + 20000 (AR) + 15000 + 10000 (credit usage) = 75000
        $this->assertEquals(75000, $outstanding);
    }

    public function test_excludes_paid_invoices_from_outstanding_calculation(): void
    {
        CustomerInvoice::factory()
            ->forOrganization($this->organization)
            ->create([
                'status' => CustomerInvoiceStatus::PAID,
                'total_amount' => 50000,
                'paid_amount' => 50000, // outstanding = 0
            ]);

        CreditUsage::factory()
            ->forOrganization($this->organization)
            ->released()
            ->withAmount(10000)
            ->create();

        $outstanding = $this->creditControlService->getCurrentOutstanding($this->organization->id);

        $this->assertEquals(0, $outstanding);
    }

    public function test_provides_comprehensive_credit_status(): void
    {
        CreditLimit::factory()
            ->forOrganization($this->organization)
            ->withLimit(200000)
            ->create();

        CustomerInvoice::factory()
            ->forOrganization($this->organization)
            ->create([
                'status' => CustomerInvoiceStatus::ISSUED,
                'total_amount' => 80000,
                'paid_amount' => 0, // outstanding = 80000
            ]);

        $status = $this->creditControlService->getCreditStatus($this->organization->id);

        $this->assertEquals($this->organization->id, $status['organization_id']);
        $this->assertTrue($status['has_credit_limit']);
        $this->assertEquals(200000, $status['credit_limit']);
        $this->assertEquals(80000, $status['current_outstanding']);
        $this->assertEquals(120000, $status['available_credit']);
        $this->assertEquals(40, $status['utilization_percentage']); // 80000/200000 * 100
        $this->assertFalse($status['has_overdue']);
        $this->assertTrue($status['can_create_po']);
    }

    public function test_handles_inactive_credit_limits(): void
    {
        CreditLimit::factory()
            ->forOrganization($this->organization)
            ->inactive()
            ->withLimit(50000)
            ->create();

        $result = $this->creditControlService->canCreatePO($this->organization->id, 75000);

        $this->assertTrue($result['allowed']);
        $this->assertEquals('PO dapat dibuat.', $result['message']);
    }

    public function test_isolates_credit_control_by_organization(): void
    {
        $otherOrg = Organization::factory()->create();

        // Other org has overdue invoice
        SupplierInvoice::factory()
            ->forOrganization($otherOrg)
            ->create([
                'status' => SupplierInvoiceStatus::OVERDUE,
                'due_date' => now()->subDays(5),
            ]);

        // Current org should not be affected
        $result = $this->creditControlService->canCreatePO($this->organization->id, 50000);

        $this->assertTrue($result['allowed']);
        $this->assertEquals('PO dapat dibuat.', $result['message']);
    }

    public function test_handles_zero_credit_limit(): void
    {
        CreditLimit::factory()
            ->forOrganization($this->organization)
            ->withLimit(0)
            ->create();

        $result = $this->creditControlService->canCreatePO($this->organization->id, 1000);

        $this->assertFalse($result['allowed']);
        $this->assertEquals('credit_limit_exceeded', $result['reason']);
    }

    public function test_check_credit_limit_method_directly(): void
    {
        CreditLimit::factory()
            ->forOrganization($this->organization)
            ->withLimit(100000)
            ->create();

        $result = $this->creditControlService->checkCreditLimit($this->organization->id, 50000);

        $this->assertTrue($result['allowed']);
        $this->assertEquals('Credit limit check passed.', $result['message']);
        $this->assertEquals(100000, $result['details']['credit_limit']);
        $this->assertEquals(100000, $result['details']['available_credit']);
    }

    public function test_credit_status_without_limit(): void
    {
        $status = $this->creditControlService->getCreditStatus($this->organization->id);

        $this->assertEquals($this->organization->id, $status['organization_id']);
        $this->assertFalse($status['has_credit_limit']);
        $this->assertNull($status['credit_limit']);
        $this->assertEquals(0, $status['current_outstanding']);
        $this->assertNull($status['available_credit']);
        $this->assertEquals(0, $status['utilization_percentage']);
        $this->assertFalse($status['has_overdue']);
        $this->assertTrue($status['can_create_po']);
    }
}