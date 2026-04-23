<?php

namespace App\StateMachines;

use App\Models\PurchaseOrder;

/**
 * Purchase Order Finite State Machine
 *
 * States: draft → submitted → approved → [partially_received] → completed
 *                          ↘ rejected → draft
 *
 * Delivery tracking happens OUTSIDE the system.
 */
class PurchaseOrderStateMachine extends AbstractStateMachine
{
    protected function transitions(): array
    {
        return [

            // ── DRAFT ────────────────────────────────────────────────────────
            PurchaseOrder::STATUS_DRAFT => [
                [
                    'to'        => PurchaseOrder::STATUS_SUBMITTED,
                    'trigger'   => 'submit_po',
                    'roles'     => ['Healthcare User', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($po) => $po !== null && $po->items()->count() > 0,
                    'guard_msg' => 'PO harus memiliki minimal satu item sebelum dapat diajukan.',
                    'reversible'=> false,
                ],
            ],

            // ── SUBMITTED ────────────────────────────────────────────────────
            PurchaseOrder::STATUS_SUBMITTED => [
                [
                    'to'        => PurchaseOrder::STATUS_APPROVED,
                    'trigger'   => 'approve_po',
                    'roles'     => ['Approver', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($po, $actor) => $actor === null || $po->created_by !== $actor->id,
                    'guard_msg' => 'Anda tidak dapat menyetujui PO yang Anda buat sendiri.',
                    'reversible'=> false,
                ],
                [
                    'to'        => PurchaseOrder::STATUS_REJECTED,
                    'trigger'   => 'reject_po',
                    'roles'     => ['Approver', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($po, $actor) => $actor === null || $po->created_by !== $actor->id,
                    'guard_msg' => 'Anda tidak dapat menolak PO yang Anda buat sendiri.',
                    'reversible'=> false,
                ],
            ],

            // ── APPROVED ─────────────────────────────────────────────────────
            PurchaseOrder::STATUS_APPROVED => [
                [
                    'to'        => PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
                    'trigger'   => 'confirm_partial_receipt',
                    'roles'     => [],  // System-triggered via GoodsReceiptService
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> false,
                ],
                [
                    'to'        => PurchaseOrder::STATUS_COMPLETED,
                    'trigger'   => 'confirm_full_receipt',
                    'roles'     => [],  // System-triggered via GoodsReceiptService
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> false,
                ],
            ],

            // ── PARTIALLY RECEIVED ───────────────────────────────────────────
            PurchaseOrder::STATUS_PARTIALLY_RECEIVED => [
                [
                    'to'        => PurchaseOrder::STATUS_COMPLETED,
                    'trigger'   => 'confirm_full_receipt',
                    'roles'     => [],  // System-triggered via GoodsReceiptService
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> false,
                ],
            ],

            // ── REJECTED ─────────────────────────────────────────────────────
            PurchaseOrder::STATUS_REJECTED => [
                [
                    'to'        => PurchaseOrder::STATUS_DRAFT,
                    'trigger'   => 'reopen_po',
                    'roles'     => ['Healthcare User', 'Admin Pusat', 'Super Admin'],
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> true,
                ],
            ],

            // ── COMPLETED ────────────────────────────────────────────────────
            // Terminal state — no outgoing transitions
            PurchaseOrder::STATUS_COMPLETED => [],
        ];
    }
}
