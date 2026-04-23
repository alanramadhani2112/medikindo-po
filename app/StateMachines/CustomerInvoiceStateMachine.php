<?php

namespace App\StateMachines;

use App\Enums\CustomerInvoiceStatus;

/**
 * Customer Invoice (AR) Finite State Machine
 *
 * States: draft → issued → partial_paid → paid
 *                       ↘ void
 *
 * Immutability rule: once issued, financial fields cannot be changed.
 */
class CustomerInvoiceStateMachine extends AbstractStateMachine
{
    protected function transitions(): array
    {
        return [

            // ── DRAFT ────────────────────────────────────────────────────────
            CustomerInvoiceStatus::DRAFT->value => [
                [
                    'to'        => CustomerInvoiceStatus::ISSUED->value,
                    'trigger'   => 'issue_invoice',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($inv) => $inv !== null && $inv->total_amount > 0,
                    'guard_msg' => 'Invoice tidak dapat diterbitkan dengan total Rp 0.',
                    'reversible'=> false,
                ],
                [
                    'to'        => CustomerInvoiceStatus::VOID->value,
                    'trigger'   => 'void_invoice',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> false,
                ],
            ],

            // ── ISSUED ───────────────────────────────────────────────────────
            CustomerInvoiceStatus::ISSUED->value => [
                [
                    'to'        => CustomerInvoiceStatus::PARTIAL_PAID->value,
                    'trigger'   => 'payment_received',
                    'roles'     => [],  // System-triggered via PaymentService
                    'guard'     => fn($inv, $actor, $ctx) =>
                        isset($ctx['amount']) && $ctx['amount'] < $inv->total_amount,
                    'guard_msg' => 'Pembayaran parsial harus kurang dari total invoice.',
                    'reversible'=> false,
                ],
                [
                    'to'        => CustomerInvoiceStatus::PAID->value,
                    'trigger'   => 'payment_received',
                    'roles'     => [],  // System-triggered via PaymentService
                    'guard'     => fn($inv, $actor, $ctx) =>
                        isset($ctx['amount']) && $ctx['amount'] >= $inv->total_amount,
                    'guard_msg' => 'Jumlah pembayaran harus >= total invoice untuk status Lunas.',
                    'reversible'=> false,
                ],
                [
                    'to'        => CustomerInvoiceStatus::VOID->value,
                    'trigger'   => 'void_invoice',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($inv) => (float) $inv->paid_amount === 0.0,
                    'guard_msg' => 'Invoice yang sudah ada pembayaran tidak dapat dibatalkan.',
                    'reversible'=> false,
                ],
            ],

            // ── PARTIAL PAID ─────────────────────────────────────────────────
            CustomerInvoiceStatus::PARTIAL_PAID->value => [
                [
                    'to'        => CustomerInvoiceStatus::PAID->value,
                    'trigger'   => 'payment_received',
                    'roles'     => [],  // System-triggered via PaymentService
                    'guard'     => fn($inv, $actor, $ctx) =>
                        isset($ctx['remaining']) && $ctx['remaining'] <= 0,
                    'guard_msg' => 'Sisa tagihan harus 0 untuk status Lunas.',
                    'reversible'=> false,
                ],
            ],

            // ── PAID ─────────────────────────────────────────────────────────
            // Terminal state
            CustomerInvoiceStatus::PAID->value => [],

            // ── VOID ─────────────────────────────────────────────────────────
            // Terminal state
            CustomerInvoiceStatus::VOID->value => [],
        ];
    }
}
