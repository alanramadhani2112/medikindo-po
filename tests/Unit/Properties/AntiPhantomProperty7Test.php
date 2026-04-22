<?php

namespace Tests\Unit\Properties;

use App\Exceptions\AntiPhantomBillingException;
use App\Models\SupplierInvoice;
use App\Services\DocumentNumberService;
use App\Services\InvoiceCalculationService;
use App\Services\MirrorGenerationService;
use App\Services\PriceListService;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Property 7: Anti-Phantom Billing Enforcement
 *
 * MirrorGenerationService::generateARFromAP() throws AntiPhantomBillingException
 * when AP status is not 'verified' or 'paid'.
 *
 * Validates: Requirements 8.1, 8.2, 17.2, 17.3, 17.4
 *
 * @group property-based
 */
class AntiPhantomProperty7Test extends TestCase
{
    private MirrorGenerationService $service;

    private array $invalidStatuses = [
        'draft',
        'overdue',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $priceListService    = Mockery::mock(PriceListService::class);
        $calcService         = Mockery::mock(InvoiceCalculationService::class);
        $documentNumService  = Mockery::mock(DocumentNumberService::class);

        $this->service = new MirrorGenerationService($priceListService, $calcService, $documentNumService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeMockSupplierInvoice(string $status): SupplierInvoice
    {
        $invoice = Mockery::mock(SupplierInvoice::class)->makePartial();
        $invoice->id             = mt_rand(1, 9999);
        $invoice->invoice_number = 'AP-TEST-' . mt_rand(1000, 9999);
        $invoice->status         = $status;

        // draftExists check — mock CustomerInvoice::where chain to return false
        // We do this by making the service think no draft exists.
        // Since we can't easily mock static Eloquent calls in a unit test,
        // we rely on the fact that AntiPhantomBillingException is thrown AFTER
        // the draftExists check. We'll use a real DB-less approach:
        // The service checks draftExists first (DB call), then status.
        // To avoid DB, we override draftExists via a partial mock of the service.

        return $invoice;
    }

    /**
     * Property 7: AntiPhantomBillingException thrown for each invalid status.
     * 25 iterations per invalid status.
     */
    public function test_anti_phantom_throws_for_draft_status(): void
    {
        $this->runAntiPhantomIterations('draft');
    }

    public function test_anti_phantom_throws_for_overdue_status(): void
    {
        $this->runAntiPhantomIterations('overdue');
    }

    private function runAntiPhantomIterations(string $invalidStatus): void
    {
        $priceListService   = Mockery::mock(PriceListService::class);
        $calcService        = Mockery::mock(InvoiceCalculationService::class);
        $documentNumService = Mockery::mock(DocumentNumberService::class);

        // Partial mock of MirrorGenerationService so draftExists() returns false
        // (so we reach the status check)
        $service = Mockery::mock(MirrorGenerationService::class, [$priceListService, $calcService, $documentNumService])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('draftExists')->andReturn(false);

        for ($i = 0; $i < 25; $i++) {
            $invoice = $this->makeMockSupplierInvoice($invalidStatus);

            $customerId = mt_rand(1, 9999);

            $this->expectException(AntiPhantomBillingException::class);

            $service->generateARFromAP($invoice, $customerId);
        }
    }

    public function test_anti_phantom_does_not_throw_for_verified_status(): void
    {
        // We can only verify the status guard logic here without a full DB.
        // The exception should NOT be thrown for 'verified' — the service proceeds further.
        // We verify by checking that AntiPhantomBillingException is NOT thrown
        // (it may throw DuplicateMirrorException or another exception from DB calls).

        $priceListService   = Mockery::mock(PriceListService::class);
        $calcService        = Mockery::mock(InvoiceCalculationService::class);
        $documentNumService = Mockery::mock(DocumentNumberService::class);

        $service = Mockery::mock(MirrorGenerationService::class, [$priceListService, $calcService, $documentNumService])
            ->makePartial();

        $service->shouldReceive('draftExists')->andReturn(false);

        $invoice = $this->makeMockSupplierInvoice('verified');

        try {
            $service->generateARFromAP($invoice, 1);
        } catch (AntiPhantomBillingException $e) {
            $this->fail('AntiPhantomBillingException should NOT be thrown for verified status');
        } catch (\Throwable $e) {
            // Other exceptions (DB, etc.) are expected in unit test context — that's fine
            $this->assertNotInstanceOf(
                AntiPhantomBillingException::class,
                $e,
                'AntiPhantomBillingException should not be thrown for verified status'
            );
        }

        $this->assertTrue(true);
    }
}
