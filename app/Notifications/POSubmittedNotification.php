<?php

namespace App\Notifications;

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class POSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PurchaseOrder $po) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isCreator = $notifiable->id === $this->po->created_by;
        $subject   = $isCreator 
            ? "Submission Received — PO #{$this->po->po_number}"
            : "Action Required — PO #{$this->po->po_number} Awaiting Approval";

        $greeting = "Hello {$notifiable->name},";
        $line1    = $isCreator
            ? "Your Purchase Order has been successfully submitted and is currently under review."
            : "A new Purchase Order requires your approval.";

        $narcoticsNote = $this->po->has_narcotics
            ? '⚠️ This PO contains **narcotic items** and requires Level 2 narcotics compliance approval.'
            : '';

        return (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line($line1)
            ->line("**PO Number:** {$this->po->po_number}")
            ->line("**Organization:** {$this->po->organization?->name}")
            ->line("**Supplier:** {$this->po->supplier?->name}")
            ->line("**Total Amount:** Rp " . number_format($this->po->total_amount, 0, ',', '.'))
            ->when($narcoticsNote, fn($msg) => $msg->line($narcoticsNote))
            ->line("You can track the progress of this PO using the link below.")
            ->action('View Purchase Order', route('web.po.show', $this->po))
            ->salutation("Regards, Medikindo PO System");
    }

    public function toArray(object $notifiable): array
    {
        $isCreator = $notifiable->id === $this->po->created_by;

        return [
            'po_id'         => $this->po->id,
            'po_number'     => $this->po->po_number,
            'has_narcotics' => $this->po->has_narcotics,
            'total_amount'  => $this->po->total_amount,
            'title'         => $isCreator ? 'PO Berhasil Diajukan' : 'PO Baru Menunggu Persetujuan',
            'message'       => $isCreator 
                ? "PO #{$this->po->po_number} Anda telah berhasil diajukan dan sedang diproses."
                : "PO #{$this->po->po_number} telah diajukan dan menunggu persetujuan Anda.",
            'url'           => route('web.po.show', $this->po),
            'icon'          => $isCreator ? 'success' : 'info',
            'type'          => 'info'
        ];
    }
}
