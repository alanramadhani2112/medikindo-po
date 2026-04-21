<?php

namespace App\Services;

use App\Models\BankAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BankAccountService
{
    // Max 3 default banks per type (receive / send)
    const MAX_DEFAULTS_PER_TYPE = 3;

    public function __construct(private readonly AuditService $auditService) {}

    // ── CRUD ───────────────────────────────────────────────────────────────

    public function create(array $data): BankAccount
    {
        $account = BankAccount::create([
            'bank_name'           => $data['bank_name'],
            'bank_code'           => $data['bank_code'] ?? null,
            'account_number'      => $data['account_number'],
            'account_holder_name' => $data['account_holder_name'],
            'account_type'        => $data['account_type'] ?? 'both',
            'is_active'           => true,
            'is_default'          => false,
            'default_for_receive' => false,
            'default_for_send'    => false,
            'default_priority'    => 0,
            'notes'               => $data['notes'] ?? null,
        ]);

        $this->auditService->log(
            action: 'bank_account_created',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: [
                'bank_name'    => $account->bank_name,
                'account_number' => $account->account_number,
                'account_type' => $account->account_type,
            ],
        );

        return $account;
    }

    public function update(BankAccount $account, array $data): BankAccount
    {
        $account->update([
            'bank_name'           => $data['bank_name'],
            'bank_code'           => $data['bank_code'] ?? null,
            'account_number'      => $data['account_number'],
            'account_holder_name' => $data['account_holder_name'],
            'account_type'        => $data['account_type'] ?? $account->account_type,
            'notes'               => $data['notes'] ?? null,
        ]);

        $this->auditService->log(
            action: 'bank_account_updated',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: [
                'bank_name'    => $account->bank_name,
                'account_number' => $account->account_number,
                'account_type' => $account->account_type,
            ],
        );

        return $account->fresh();
    }

    public function delete(BankAccount $account): void
    {
        if (!$account->canBeDeleted()) {
            throw new \RuntimeException(
                "Rekening '{$account->bank_name} - {$account->account_number}' tidak dapat dihapus karena masih digunakan oleh invoice atau transaksi pembayaran."
            );
        }

        $id   = $account->id;
        $meta = ['bank_name' => $account->bank_name, 'account_number' => $account->account_number];

        $account->delete();

        $this->auditService->log(
            action: 'bank_account_deleted',
            entityType: BankAccount::class,
            entityId: $id,
            metadata: $meta,
        );
    }

    // ── Default Management ─────────────────────────────────────────────────

    /**
     * Set as default for receiving payments (AR — from RS/Klinik).
     * Max 3 defaults allowed. Priority = next available slot (1, 2, or 3).
     */
    public function setDefaultForReceive(BankAccount $account): void
    {
        if (!$account->is_active) {
            throw new \InvalidArgumentException('Hanya rekening aktif yang dapat dijadikan default.');
        }

        if (!$account->canReceive()) {
            throw new \InvalidArgumentException(
                "Rekening dengan tipe '{$account->getAccountTypeLabel()}' tidak dapat menerima pembayaran masuk. Ubah tipe rekening terlebih dahulu."
            );
        }

        $currentCount = BankAccount::where('default_for_receive', true)->where('is_active', true)->count();

        if ($account->default_for_receive) {
            // Already set — remove it
            $account->update(['default_for_receive' => false, 'default_priority' => 0]);
            $this->reorderPriorities('receive');
            return;
        }

        if ($currentCount >= self::MAX_DEFAULTS_PER_TYPE) {
            throw new \InvalidArgumentException(
                'Maksimal ' . self::MAX_DEFAULTS_PER_TYPE . ' rekening default untuk penerimaan. Hapus salah satu terlebih dahulu.'
            );
        }

        $nextPriority = $currentCount + 1;

        DB::transaction(function () use ($account, $nextPriority) {
            $account->update([
                'default_for_receive' => true,
                'default_priority'    => $nextPriority,
                'is_default'          => $nextPriority === 1, // legacy compat
            ]);
        });

        $this->auditService->log(
            action: 'bank_account_set_default_receive',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: ['bank_name' => $account->bank_name, 'priority' => $nextPriority],
        );
    }

    /**
     * Set as default for sending payments (AP — to Supplier).
     * Max 3 defaults allowed.
     */
    public function setDefaultForSend(BankAccount $account): void
    {
        if (!$account->is_active) {
            throw new \InvalidArgumentException('Hanya rekening aktif yang dapat dijadikan default.');
        }

        if (!$account->canSend()) {
            throw new \InvalidArgumentException(
                "Rekening dengan tipe '{$account->getAccountTypeLabel()}' tidak dapat mengirim pembayaran keluar. Ubah tipe rekening terlebih dahulu."
            );
        }

        $currentCount = BankAccount::where('default_for_send', true)->where('is_active', true)->count();

        if ($account->default_for_send) {
            // Already set — remove it
            $account->update(['default_for_send' => false]);
            return;
        }

        if ($currentCount >= self::MAX_DEFAULTS_PER_TYPE) {
            throw new \InvalidArgumentException(
                'Maksimal ' . self::MAX_DEFAULTS_PER_TYPE . ' rekening default untuk pengiriman. Hapus salah satu terlebih dahulu.'
            );
        }

        DB::transaction(function () use ($account) {
            $account->update(['default_for_send' => true]);
        });

        $this->auditService->log(
            action: 'bank_account_set_default_send',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: ['bank_name' => $account->bank_name],
        );
    }

    /** Legacy: set single default (backward compat) */
    public function setDefault(BankAccount $account): void
    {
        if (!$account->is_active) {
            throw new \InvalidArgumentException('Hanya rekening aktif yang dapat dijadikan default.');
        }

        DB::transaction(function () use ($account) {
            BankAccount::where('is_default', true)->update(['is_default' => false]);
            $account->update([
                'is_default'          => true,
                'default_for_receive' => true,
                'default_for_send'    => true,
                'default_priority'    => 1,
            ]);
        });
    }

    public function deactivate(BankAccount $account): void
    {
        $updates = [
            'is_active'           => false,
            'is_default'          => false,
            'default_for_receive' => false,
            'default_for_send'    => false,
            'default_priority'    => 0,
        ];

        $account->update($updates);

        $this->auditService->log(
            action: 'bank_account_deactivated',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: ['bank_name' => $account->bank_name],
        );
    }

    public function activate(BankAccount $account): void
    {
        $account->update(['is_active' => true]);

        $this->auditService->log(
            action: 'bank_account_activated',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: ['bank_name' => $account->bank_name],
        );
    }

    // ── Queries ────────────────────────────────────────────────────────────

    public function getActiveAccounts(): Collection
    {
        return BankAccount::active()->orderBy('default_priority')->orderBy('bank_name')->get();
    }

    public function getReceiveAccounts(): Collection
    {
        return BankAccount::forReceive()->orderBy('default_for_receive', 'desc')->orderBy('default_priority')->orderBy('bank_name')->get();
    }

    public function getSendAccounts(): Collection
    {
        return BankAccount::forSend()->orderBy('default_for_send', 'desc')->orderBy('bank_name')->get();
    }

    public function getDefaultReceiveAccount(): ?BankAccount
    {
        return BankAccount::defaultReceive()->first();
    }

    public function getDefaultSendAccount(): ?BankAccount
    {
        return BankAccount::defaultSend()->first();
    }

    public function getDefaultAccount(): ?BankAccount
    {
        return BankAccount::where('is_default', true)->where('is_active', true)->first();
    }

    // ── Cashflow Summary ───────────────────────────────────────────────────

    /**
     * Get cashflow summary per bank account.
     */
    public function getCashflowSummary(): Collection
    {
        return BankAccount::active()
            ->with(['incomingPayments', 'outgoingPayments'])
            ->get()
            ->map(function (BankAccount $bank) {
                $incoming = $bank->incomingPayments()
                    ->whereIn('status', ['completed', 'confirmed'])
                    ->sum('amount');
                $outgoing = $bank->outgoingPayments()
                    ->whereIn('status', ['completed', 'confirmed'])
                    ->sum('amount');

                return [
                    'id'              => $bank->id,
                    'bank_name'       => $bank->bank_name,
                    'account_number'  => $bank->account_number,
                    'account_type'    => $bank->account_type,
                    'type_label'      => $bank->getAccountTypeLabel(),
                    'total_incoming'  => (float) $incoming,
                    'total_outgoing'  => (float) $outgoing,
                    'net_cashflow'    => (float) $incoming - (float) $outgoing,
                    'current_balance' => $bank->current_balance,
                ];
            });
    }

    // ── Private Helpers ────────────────────────────────────────────────────

    private function reorderPriorities(string $type): void
    {
        $field = "default_for_{$type}";
        $accounts = BankAccount::where($field, true)
            ->where('is_active', true)
            ->orderBy('default_priority')
            ->get();

        foreach ($accounts as $i => $acc) {
            $acc->update(['default_priority' => $i + 1]);
        }
    }
}
