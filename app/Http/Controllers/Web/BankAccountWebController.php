<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Models\BankAccount;
use App\Services\BankAccountService;
use Illuminate\Http\Request;

class BankAccountWebController extends Controller
{
    public function __construct(private readonly BankAccountService $bankAccountService) {}

    public function index()
    {
        $accounts = BankAccount::orderBy('is_default', 'desc')
            ->orderBy('is_active', 'desc')
            ->orderBy('bank_name')
            ->paginate(15);

        return view('bank-accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('bank-accounts.create');
    }

    public function store(StoreBankAccountRequest $request)
    {
        $this->bankAccountService->create($request->validated());

        return redirect()
            ->route('web.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil ditambahkan.');
    }

    public function edit(BankAccount $bankAccount)
    {
        return view('bank-accounts.edit', compact('bankAccount'));
    }

    public function update(UpdateBankAccountRequest $request, BankAccount $bankAccount)
    {
        $this->bankAccountService->update($bankAccount, $request->validated());

        return redirect()
            ->route('web.bank-accounts.index')
            ->with('success', 'Rekening bank berhasil diperbarui.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        try {
            $this->bankAccountService->delete($bankAccount);
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('success', 'Rekening bank berhasil dihapus.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('error', $e->getMessage());
        }
    }

    public function setDefault(BankAccount $bankAccount)
    {
        try {
            $this->bankAccountService->setDefault($bankAccount);
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('success', "Rekening {$bankAccount->bank_name} - {$bankAccount->account_number} dijadikan default.");
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('error', $e->getMessage());
        }
    }

    public function toggleActive(BankAccount $bankAccount)
    {
        if ($bankAccount->is_active) {
            $this->bankAccountService->deactivate($bankAccount);
            $msg = "Rekening {$bankAccount->bank_name} dinonaktifkan.";
        } else {
            $this->bankAccountService->activate($bankAccount);
            $msg = "Rekening {$bankAccount->bank_name} diaktifkan.";
        }

        return redirect()
            ->route('web.bank-accounts.index')
            ->with('success', $msg);
    }
}
