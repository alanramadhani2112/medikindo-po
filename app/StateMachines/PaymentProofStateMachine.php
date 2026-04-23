<?php

namespace App\StateMachines;

use App\Enums\PaymentProofStatus;

/**
 * Payment Proof Finite State Machine
 *
 * States:
 *   submitted → verified → approved  (main flow)
 *   submitted → rejected → resubmitted → verified → approved
 *   submitted → recalled             (Healthcare withdraws)
 *   approved  → recalled             (Super Admin correction creates new proof)
 */
class PaymentProofStateMachine extends AbstractStateMachine
{
    protected function transitions(): array
    {
        return [

            // ── SUBMITTED ────────────────────────────────────────────────────
            PaymentProofStatus::SUBMITTED->value => [
                [
                    'to'        => PaymentProofStatus::VERIFIED->value,
                    'trigger'   => 'verify_proof',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($proof, $actor) =>
                        $actor === null || $proof->submitted_by !== $actor->id,
                    'guard_msg' => 'Anda tidak dapat memverifikasi bukti pembayaran yang Anda ajukan sendiri.',
                    'reversible'=> false,
                ],
                [
                    'to'        => PaymentProofStatus::REJECTED->value,
                    'trigger'   => 'reject_proof',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> false,
                ],
                [
                    'to'        => PaymentProofStatus::RECALLED->value,
                    'trigger'   => 'recall_proof',
                    'roles'     => ['Healthcare User', 'Super Admin'],
                    'guard'     => fn($proof, $actor) =>
                        $actor === null || $proof->submitted_by === $actor->id || $actor->isSuperAdmin(),
                    'guard_msg' => 'Hanya pengirim asli yang dapat menarik bukti pembayaran.',
                    'reversible'=> false,
                ],
            ],

            // ── RESUBMITTED ──────────────────────────────────────────────────
            PaymentProofStatus::RESUBMITTED->value => [
                [
                    'to'        => PaymentProofStatus::VERIFIED->value,
                    'trigger'   => 'verify_proof',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($proof, $actor) =>
                        $actor === null || $proof->submitted_by !== $actor->id,
                    'guard_msg' => 'Anda tidak dapat memverifikasi bukti pembayaran yang Anda ajukan sendiri.',
                    'reversible'=> false,
                ],
                [
                    'to'        => PaymentProofStatus::REJECTED->value,
                    'trigger'   => 'reject_proof',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> false,
                ],
            ],

            // ── VERIFIED ─────────────────────────────────────────────────────
            PaymentProofStatus::VERIFIED->value => [
                [
                    'to'        => PaymentProofStatus::APPROVED->value,
                    'trigger'   => 'approve_proof',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => fn($proof, $actor) =>
                        $actor === null || $proof->submitted_by !== $actor->id,
                    'guard_msg' => 'Anda tidak dapat menyetujui bukti pembayaran yang Anda ajukan sendiri.',
                    'reversible'=> false,
                ],
                [
                    'to'        => PaymentProofStatus::REJECTED->value,
                    'trigger'   => 'reject_proof',
                    'roles'     => ['Finance', 'Admin Pusat', 'Super Admin'],
                    'guard'     => null,
                    'guard_msg' => '',
                    'reversible'=> false,
                ],
            ],

            // ── REJECTED ─────────────────────────────────────────────────────
            PaymentProofStatus::REJECTED->value => [
                [
                    'to'        => PaymentProofStatus::RESUBMITTED->value,
                    'trigger'   => 'resubmit_proof',
                    'roles'     => ['Healthcare User', 'Super Admin'],
                    'guard'     => fn($proof, $actor) =>
                        $actor === null || $proof->submitted_by === $actor->id || $actor->isSuperAdmin(),
                    'guard_msg' => 'Hanya pengirim asli yang dapat mengajukan ulang bukti pembayaran.',
                    'reversible'=> true,
                ],
            ],

            // ── APPROVED ─────────────────────────────────────────────────────
            // Terminal state — Super Admin correction creates a NEW proof, not transitions this one
            PaymentProofStatus::APPROVED->value => [],

            // ── RECALLED ─────────────────────────────────────────────────────
            // Terminal state
            PaymentProofStatus::RECALLED->value => [],
        ];
    }
}
