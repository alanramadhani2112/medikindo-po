<?php

namespace App\StateMachines;

use App\Enums\SupplierInvoiceStatus;

/**
 * Supplier Invoice (AP) Finite State Machine
 *
 * States: draft → verified → paid
 *              ↘ overdue → verified → paid
 *
 * Payment OUT only allowed when status = verified or overdue.
 */
class SupplierInvoiceStateMachine extends AbstractStateMachine
{
    protected function transitions(): array
    {
        return [

            // ── DRAFT ────────────────────────────────────────────────────────
            SupplierInvoiceStatus::DRAFT->value => [
                [
                    'to'        => SupplierInvoiceStatus::VERIFIED->value,
                    'trigger'   => 'verify_invoice',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($inv) => $inv !== null && $inv->total_amount > 0,
                    'guard_msg' => 'Invoice tidak dapat diverifikasi dengan total Rp 0.',
                    'reversible'=> false,
                ],
                [
                    'to'        => SupplierInvoiceStatus::OVERDUE->value,
                    'trigger'   => 'mark_overdue',
                    'roles'     => [],  // System-triggered via scheduled job
                    'guard'     => fn($inv) => $inv !== null && $inv->due_date !== null && $inv->due_date->isPast(),
                    'guard_msg' => 'Invoice belum melewati tanggal jatuh tempo.',
                    'reversible'=> false,
                ],
            ],

            // ── VERIFIED ─────────────────────────────────────────────────────
            SupplierInvoiceStatus::VERIFIED->value => [
                [
                    'to'        => SupplierInvoiceStatus::PAID->value,
                    'trigger'   => 'payment_out',
                    'roles'     => [],  // System-triggered via PaymentService
                    'guard'     => fn($inv, $actor, $ctx) =>
                        isset($ctx['amount']) && $ctx['amount'] >= ((float)$inv->total_amount - (float)$inv->paid_amount),
                    'guard_msg' => 'Jumlah pembayaran tidak mencukupi untuk melunasi invoice.',
                    'reversible'=> false,
                ],
                [
                    'to'        => SupplierInvoiceStatus::OVERDUE->value,
                    'trigger'   => 'mark_overdue',
                    'roles'     => [],  // System-triggered via scheduled job
                    'guard'     => fn($inv) => $inv !== null && $inv->due_date !== null && $inv->due_date->isPast(),
                    'guard_msg' => 'Invoice belum melewati tanggal jatuh tempo.',
                    'reversible'=> false,
                ],
            ],

            // ── OVERDUE ──────────────────────────────────────────────────────
            SupplierInvoiceStatus::OVERDUE->value => [
                [
                    'to'        => SupplierInvoiceStatus::VERIFIED->value,
                    'trigger'   => 'verify_invoice',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> false,
                ],
                [
                    'to'        => SupplierInvoiceStatus::PAID->value,
                    'trigger'   => 'payment_out',
                    'roles'     => [],  // System-triggered via PaymentService
                    'guard'     => fn($inv, $actor, $ctx) =>
                        isset($ctx['amount']) && $ctx['amount'] >= ((float)$inv->total_amount - (float)$inv->paid_amount),
                    'guard_msg' => 'Jumlah pembayaran tidak mencukupi untuk melunasi invoice.',
                    'reversible'=> false,
                ],
            ],

            // ── PAID ─────────────────────────────────────────────────────────
            // Terminal state
            SupplierInvoiceStatus::PAID->value => [],
        ];
    }
}
