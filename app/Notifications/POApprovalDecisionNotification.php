<?php

namespace App\Notifications;

use App\Models\Approval;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class POApprovalDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly PurchaseOrder $po,
        private readonly Approval $approval,
        private readonly User $approver,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $decision = strtoupper($this->approval->status);
        $emoji    = match ($this->approval->status) {
            Approval::STATUS_APPROVED => '✅',
            Approval::STATUS_REJECTED => '❌',
            default                   => '📋',
        };

        $levelLabel = $this->approval->level === Approval::LEVEL_NARCOTICS
            ? 'Level 2 — Narcotics Compliance'
            : 'Level 1 — Standard';

        $message = (new MailMessage)
            ->subject("{$emoji} PO #{$this->po->po_number} has been {$this->approval->status}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your Purchase Order has received an approval decision.")
            ->line("**PO Number:** {$this->po->po_number}")
            ->line("**Approval Level:** {$levelLabel}")
            ->line("**Decision:** {$decision}")
            ->line("**Approver:** {$this->approver->name}");

        if ($this->approval->notes) {
            $message->line("**Notes:** {$this->approval->notes}");
        }

        if ($this->approval->status === Approval::STATUS_REJECTED) {
            $message->line("The PO has been **rejected**. You may review the notes and create a new PO.");
        } elseif ($this->po->status === PurchaseOrder::STATUS_APPROVED) {
            $message->line("All approval levels are complete. You may now send this PO to the supplier.");
        }

        return $message->salutation("Regards, Medikindo PO System");
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->approval->status === Approval::STATUS_APPROVED ? 'Disetujui' : 'Ditolak';
        $icon = $this->approval->status === Approval::STATUS_APPROVED ? 'success' : 'danger';

        return [
            'po_id'           => $this->po->id,
            'po_number'       => $this->po->po_number,
            'approval_level'  => $this->approval->level,
            'decision'        => $this->approval->status,
            'approver_name'   => $this->approver->name,
            'title'           => "PO #{$this->po->po_number} {$statusLabel}",
            'message'         => "Purchase Order #{$this->po->po_number} Anda telah {$statusLabel} oleh {$this->approver->name}.",
            'url'             => route('web.po.show', $this->po),
            'icon'            => $icon,
            'type'            => $icon === 'success' ? 'success' : 'error'
        ];
    }
}
