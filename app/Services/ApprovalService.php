<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\POApprovalDecisionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function __construct(
        private readonly AuditService $auditService,
        private readonly ValidationService $validationService,
        private readonly CreditControlService $creditControlService,
    ) {}

    /**
     * Initialise approval records when a PO is submitted.
     * Always creates level 1 (standard). Level 2 (narcotics) if required.
     */
    public function initializeApprovals(PurchaseOrder $po): void
    {
        // Level 1 — standard approval
        $po->approvals()->firstOrCreate(
            ['level' => Approval::LEVEL_STANDARD],
            ['status' => Approval::STATUS_PENDING],
        );

        // Level 2 — narcotics compliance
        if ($po->requires_extra_approval) {
            $po->approvals()->firstOrCreate(
                ['level' => Approval::LEVEL_NARCOTICS],
                ['status' => Approval::STATUS_PENDING],
            );
        }
    }

    /**
     * Process an approval or rejection action.
     *
     * @throws ValidationException
     */
    public function process(
        PurchaseOrder $po,
        User $approver,
        int $level,
        string $decision, // 'approved' | 'rejected'
        ?string $notes = null,
    ): Approval {
        $approval = $po->approvals()
            ->where('level', $level)
            ->where('status', Approval::STATUS_PENDING)
            ->first();

        if (! $approval) {
            throw ValidationException::withMessages([
                'approval' => "No pending approval found for level {$level} on this PO.",
            ]);
        }

        return DB::transaction(function () use ($po, $approver, $approval, $decision, $notes) {
            $approval->update([
                'approver_id' => $approver->id,
                'status'      => $decision,
                'notes'       => $notes,
                'actioned_at' => now(),
            ]);

            $this->auditService->log(
                action:     "po.approval.{$decision}",
                entityType: PurchaseOrder::class,
                entityId:   $po->id,
                metadata:   [
                    'approval_id' => $approval->id,
                    'level'       => $approval->level,
                    'approver_id' => $approver->id,
                    'notes'       => $notes,
                ],
            );

            // If rejected at any level, reject the whole PO and reverse credit
            if ($decision === Approval::STATUS_REJECTED) {
                $this->creditControlService->reverseCredit($po);
                $this->updatePOStatus($po, PurchaseOrder::STATUS_REJECTED);
                $po->refresh();
                // Notify creator of rejection
                $this->notifyCreator($po, $approval, $approver);
                return $approval;
            }

            // Check if all required approvals are complete
            $this->checkAndFinalizeApproval($po);

            // Notify creator of this level's decision
            $po->refresh();
            $this->notifyCreator($po, $approval, $approver);

            return $approval;
        });
    }

    /**
     * After each approval, check if all levels are approved and advance PO status.
     */
    private function checkAndFinalizeApproval(PurchaseOrder $po): void
    {
        $totalRequired = $po->requires_extra_approval ? 2 : 1;
        $approvedCount = $po->approvals()->where('status', Approval::STATUS_APPROVED)->count();

        if ($approvedCount >= $totalRequired) {
            $this->creditControlService->billCredit($po);
            $this->updatePOStatus($po, PurchaseOrder::STATUS_APPROVED);
        }
    }

    private function updatePOStatus(PurchaseOrder $po, string $status): void
    {
        $before = $po->status;

        $timestamps = match ($status) {
            PurchaseOrder::STATUS_APPROVED => ['approved_at' => now()],
            default                        => [],
        };

        $po->update(array_merge(['status' => $status], $timestamps));

        $this->auditService->log(
            action:     "po.{$status}",
            entityType: PurchaseOrder::class,
            entityId:   $po->id,
            metadata:   [
                'po_number'     => $po->po_number,
                'before_status' => $before,
                'after_status'  => $status,
            ],
        );
    }

    private function notifyCreator(PurchaseOrder $po, Approval $approval, User $approver): void
    {
        $creator = $po->creator()->first();

        if ($creator) {
            $creator->notify(new POApprovalDecisionNotification($po, $approval, $approver));
        }

        // Also notify relevant organization staff and Super Admins
        User::role(['Super Admin', 'Healthcare User'])->get()
            ->filter(function ($u) use ($po, $creator) {
                if ($creator && $u->id === $creator->id) return false;
                if ($u->hasRole('Super Admin')) return true;
                return $u->organization_id === $po->organization_id;
            })
            ->each(fn(User $u) => $u->notify(new POApprovalDecisionNotification($po, $approval, $approver)));
    }
}
