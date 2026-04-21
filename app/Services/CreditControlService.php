<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\CustomerInvoice;
use App\Models\CreditLimit;
use App\Enums\CustomerInvoiceStatus;
use Illuminate\Support\Facades\Log;

class CreditControlService
{
    protected OverdueService $overdueService;

    public function __construct(OverdueService $overdueService)
    {
        $this->overdueService = $overdueService;
    }

    /**
     * Reserve credit when PO is submitted.
     * Throws DomainException if credit limit exceeded or overdue invoices exist.
     */
    public function reserveCredit(\App\Models\PurchaseOrder $po): void
    {
        $result = $this->canCreatePO($po->organization_id, (float) $po->total_amount);

        if (!$result['allowed']) {
            throw new \DomainException($result['message']);
        }

        Log::info('Credit reserved for PO', [
            'po_id'           => $po->id,
            'po_number'       => $po->po_number,
            'organization_id' => $po->organization_id,
            'amount'          => $po->total_amount,
        ]);
    }

    /**
     * Confirm/bill credit when PO is approved.
     * Called by ApprovalService after all approval levels pass.
     */
    public function billCredit(\App\Models\PurchaseOrder $po): void
    {
        // Credit utilisation is tracked via outstanding AR invoices.
        // No separate reservation table — this is a no-op hook for future extension.
        Log::info('Credit billed (PO approved)', [
            'po_id'           => $po->id,
            'po_number'       => $po->po_number,
            'organization_id' => $po->organization_id,
            'amount'          => $po->total_amount,
        ]);
    }

    /**
     * Reverse/release credit when PO is rejected.
     * Called by ApprovalService when any approval level rejects the PO.
     */
    public function reverseCredit(\App\Models\PurchaseOrder $po): void
    {
        Log::info('Credit reversed (PO rejected)', [
            'po_id'           => $po->id,
            'po_number'       => $po->po_number,
            'organization_id' => $po->organization_id,
            'amount'          => $po->total_amount,
        ]);
    }

    /**
     * Release credit when payment is received from customer.
     * Called by PaymentService after incoming payment is processed.
     */
    public function releaseCreditByAmount(int $organizationId, \App\Models\PurchaseOrder $purchaseOrder, float $amount): void
    {
        Log::info('Credit released by payment', [
            'organization_id' => $organizationId,
            'po_id'           => $purchaseOrder->id,
            'po_number'       => $purchaseOrder->po_number,
            'released_amount' => $amount,
        ]);
    }

    /**
     * Check if organization can create a new Purchase Order
     *
     * @param int $organizationId
     * @param float|null $poAmount Optional PO amount to check against credit limit
     * @return array ['allowed' => bool, 'reason' => string|null, 'details' => array]
     */
    public function canCreatePO(int $organizationId, ?float $poAmount = null): array
    {
        // Check 1: Overdue invoices
        if ($this->hasOverdueInvoices($organizationId)) {
            $overdueInvoices = $this->overdueService->getOverdueInvoicesByOrganization($organizationId);
            $totalOverdue = $overdueInvoices->sum('outstanding');

            Log::warning('PO creation blocked: Overdue invoices exist', [
                'organization_id' => $organizationId,
                'overdue_count' => $overdueInvoices->count(),
                'total_overdue' => $totalOverdue,
            ]);

            return [
                'allowed' => false,
                'reason' => 'overdue_invoices',
                'message' => 'Tidak dapat membuat PO. Terdapat invoice yang sudah jatuh tempo.',
                'details' => [
                    'overdue_count' => $overdueInvoices->count(),
                    'total_overdue' => $totalOverdue,
                    'invoices' => $overdueInvoices->take(5)->toArray(),
                ],
            ];
        }

        // Check 2: Credit limit (if PO amount provided)
        if ($poAmount !== null) {
            $creditCheck = $this->checkCreditLimit($organizationId, $poAmount);
            if (!$creditCheck['allowed']) {
                return $creditCheck;
            }
        }

        return [
            'allowed' => true,
            'reason' => null,
            'message' => 'PO dapat dibuat.',
            'details' => [],
        ];
    }

    /**
     * Check if organization has overdue invoices
     *
     * @param int $organizationId
     * @return bool
     */
    public function hasOverdueInvoices(int $organizationId): bool
    {
        return $this->overdueService->hasOverdueInvoices($organizationId);
    }

    /**
     * Check credit limit for organization
     *
     * @param int $organizationId
     * @param float $requestedAmount
     * @return array
     */
    public function checkCreditLimit(int $organizationId, float $requestedAmount): array
    {
        $creditLimit = CreditLimit::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->first();

        // No credit limit configured = unlimited
        if (!$creditLimit) {
            return [
                'allowed' => true,
                'reason' => null,
                'message' => 'No credit limit configured.',
                'details' => [
                    'has_limit' => false,
                ],
            ];
        }

        $currentOutstanding = $this->getCurrentOutstanding($organizationId);
        $totalExposure = $currentOutstanding + $requestedAmount;
        $availableCredit = $creditLimit->max_limit - $currentOutstanding;

        if ($totalExposure > $creditLimit->max_limit) {
            Log::warning('PO creation blocked: Credit limit exceeded', [
                'organization_id' => $organizationId,
                'credit_limit' => $creditLimit->max_limit,
                'current_outstanding' => $currentOutstanding,
                'requested_amount' => $requestedAmount,
                'total_exposure' => $totalExposure,
                'available_credit' => $availableCredit,
            ]);

            return [
                'allowed' => false,
                'reason' => 'credit_limit_exceeded',
                'message' => 'Tidak dapat membuat PO. Limit kredit akan terlampaui.',
                'details' => [
                    'credit_limit' => $creditLimit->max_limit,
                    'current_outstanding' => $currentOutstanding,
                    'requested_amount' => $requestedAmount,
                    'available_credit' => $availableCredit,
                    'shortfall' => $totalExposure - $creditLimit->max_limit,
                ],
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'message' => 'Credit limit check passed.',
            'details' => [
                'credit_limit' => $creditLimit->max_limit,
                'current_outstanding' => $currentOutstanding,
                'available_credit' => $availableCredit,
            ],
        ];
    }

    /**
     * Get current outstanding amount for organization
     *
     * @param int $organizationId
     * @return float
     */
    public function getCurrentOutstanding(int $organizationId): float
    {
        return (float) CustomerInvoice::query()
            ->where('organization_id', $organizationId)
            ->whereIn('status', [
                CustomerInvoiceStatus::ISSUED->value,
                CustomerInvoiceStatus::PARTIAL_PAID->value,
            ])
            ->get()
            ->sum('outstanding_amount');
    }

    /**
     * Get credit status summary for organization
     *
     * @param int $organizationId
     * @return array
     */
    public function getCreditStatus(int $organizationId): array
    {
        $creditLimit = CreditLimit::where('organization_id', $organizationId)
            ->where('is_active', true)
            ->first();

        $currentOutstanding = $this->getCurrentOutstanding($organizationId);
        $hasOverdue = $this->hasOverdueInvoices($organizationId);

        $status = [
            'organization_id' => $organizationId,
            'has_credit_limit' => $creditLimit !== null,
            'credit_limit' => $creditLimit?->max_limit ?? null,
            'current_outstanding' => $currentOutstanding,
            'available_credit' => $creditLimit ? ($creditLimit->max_limit - $currentOutstanding) : null,
            'utilization_percentage' => ($creditLimit && $creditLimit->max_limit > 0)
                ? (($currentOutstanding / $creditLimit->max_limit) * 100)
                : 0,
            'has_overdue' => $hasOverdue,
            'can_create_po' => !$hasOverdue,
        ];

        return $status;
    }

    /**
     * Get organizations that are blocked from creating POs
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBlockedOrganizations(): \Illuminate\Support\Collection
    {
        return Organization::all()->filter(function ($org) {
            return !$this->canCreatePO($org->id)['allowed'];
        })->map(function ($org) {
            return [
                'id' => $org->id,
                'name' => $org->name,
                'credit_status' => $this->getCreditStatus($org->id),
            ];
        });
    }
}
