<?php

namespace App\Services;

use App\Models\BankAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BankAccountService
{
    public function __construct(private readonly AuditService $auditService) {}

    // ── CRUD ───────────────────────────────────────────────────────────────

    public function create(array $data): BankAccount
    {
        $account = BankAccount::create([
            'bank_name'           => $data['bank_name'],
            'bank_code'           => $data['bank_code'] ?? null,
            'account_number'      => $data['account_number'],
            'account_holder_name' => $data['account_holder_name'],
            'is_active'           => true,
            'is_default'          => false,
            'notes'               => $data['notes'] ?? null,
        ]);

        $this->auditService->log(
            action: 'bank_account_created',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: ['bank_name' => $account->bank_name, 'account_number' => $account->account_number],
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
            'notes'               => $data['notes'] ?? null,
        ]);

        $this->auditService->log(
            action: 'bank_account_updated',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: ['bank_name' => $account->bank_name, 'account_number' => $account->account_number],
        );

        return $account->fresh();
    }

    public function delete(BankAccount $account): void
    {
        if (!$account->canBeDeleted()) {
            throw new \RuntimeException(
                "Rekening bank '{$account->bank_name} - {$account->account_number}' tidak dapat dihapus karena masih digunakan oleh invoice."
            );
        }

        $id = $account->id;
        $meta = ['bank_name' => $account->bank_name, 'account_number' => $account->account_number];

        $account->delete();

        $this->auditService->log(
            action: 'bank_account_deleted',
            entityType: BankAccount::class,
            entityId: $id,
            metadata: $meta,
        );
    }

    // ── Default & Active ───────────────────────────────────────────────────

    public function setDefault(BankAccount $account): void
    {
        if (!$account->is_active) {
            throw new \InvalidArgumentException(
                'Hanya rekening aktif yang dapat dijadikan default.'
            );
        }

        DB::transaction(function () use ($account) {
            BankAccount::where('is_default', true)->update(['is_default' => false]);
            $account->update(['is_default' => true]);
        });

        $this->auditService->log(
            action: 'bank_account_set_default',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: ['bank_name' => $account->bank_name, 'account_number' => $account->account_number],
        );
    }

    public function deactivate(BankAccount $account): void
    {
        $updates = ['is_active' => false];
        if ($account->is_default) {
            $updates['is_default'] = false;
        }

        $account->update($updates);

        $this->auditService->log(
            action: 'bank_account_deactivated',
            entityType: BankAccount::class,
            entityId: $account->id,
            metadata: ['bank_name' => $account->bank_name, 'was_default' => $account->is_default],
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
        return BankAccount::active()->orderBy('is_default', 'desc')->orderBy('bank_name')->get();
    }

    public function getDefaultAccount(): ?BankAccount
    {
        return BankAccount::where('is_default', true)->where('is_active', true)->first();
    }
}
