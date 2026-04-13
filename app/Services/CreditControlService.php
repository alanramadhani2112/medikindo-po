<?php

namespace App\Services;

use App\Models\CreditLimit;
use App\Models\CreditUsage;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use DomainException;

class CreditControlService
{
    public function __construct(private readonly AuditService $auditService) {}

    public function getAvailableCredit(int $organizationId): float
    {
        $limitRecord = CreditLimit::where('organization_id', $organizationId)->first();
        if (! $limitRecord) {
            return 0; // Or treat as no limit, but enterprise systems usually strict zero.
        }

        $usedAmount = CreditUsage::where('organization_id', $organizationId)
            ->whereIn('status', ['reserved', 'billed'])
            ->sum('amount_used');

        return max(0, $limitRecord->max_limit - $usedAmount);
    }

    public function checkCreditAvailable(int $organizationId, float $requestedAmount): void
    {
        $available = $this->getAvailableCredit($organizationId);

        if ($requestedAmount > $available) {
            throw new DomainException("Tolak: Limit kredit tidak mencukupi untuk memproses PO. Tersedia: Rp " . number_format($available, 0, ',', '.'));
        }
    }

    public function reserveCredit(PurchaseOrder $po): CreditUsage
    {
        $this->checkCreditAvailable($po->organization_id, (float) $po->total_amount);

        return DB::transaction(function () use ($po) {
            $usage = CreditUsage::create([
                'organization_id'   => $po->organization_id,
                'purchase_order_id' => $po->id,
                'amount_used'       => $po->total_amount,
                'status'            => 'reserved',
            ]);

            $this->auditService->log('credit.reserved', CreditUsage::class, $usage->id, ['amount' => $po->total_amount]);

            return $usage;
        });
    }

    public function billCredit(PurchaseOrder $po): void
    {
        DB::transaction(function () use ($po) {
            CreditUsage::where('purchase_order_id', $po->id)
                ->where('status', 'reserved')
                ->update(['status' => 'billed']);
        });
    }

    public function releaseCreditByAmount(int $organizationId, PurchaseOrder $po, float $releasedAmount): void
    {
        DB::transaction(function () use ($organizationId, $po, $releasedAmount) {
            $usage = CreditUsage::where('purchase_order_id', $po->id)
                ->where('organization_id', $organizationId)
                ->first();

            if ($usage) {
                // Determine new used amount 
                $newAmount = max(0, $usage->amount_used - $releasedAmount);
                $usage->amount_used = $newAmount;
                
                if ($newAmount == 0 && $usage->status === 'billed') {
                    $usage->status = 'released';
                }

                $usage->save();

                $this->auditService->log('credit.released', CreditUsage::class, $usage->id, ['released' => $releasedAmount]);
            }
        });
    }

    public function reverseCredit(PurchaseOrder $po): void
    {
        DB::transaction(function () use ($po) {
            CreditUsage::where('purchase_order_id', $po->id)
                ->update(['status' => 'released', 'amount_used' => 0]);
        });
    }
}
