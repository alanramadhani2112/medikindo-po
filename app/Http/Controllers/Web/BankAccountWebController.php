<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBankAccountRequest;
use App\Http\Requests\UpdateBankAccountRequest;
use App\Models\BankAccount;
use App\Models\Payment;
use App\Services\BankAccountService;
use Illuminate\Http\Request;

class BankAccountWebController extends Controller
{
    public function __construct(private readonly BankAccountService $bankAccountService) {}

    public function index()
    {
        $accounts = BankAccount::orderBy('default_for_receive', 'desc')
            ->orderBy('default_for_send', 'desc')
            ->orderBy('default_priority')
            ->orderBy('is_active', 'desc')
            ->orderBy('bank_name')
            ->paginate(20);

        // Cashflow summary per bank
        $cashflowSummary = $this->bankAccountService->getCashflowSummary();

        // Totals
        $totalIncoming = $cashflowSummary->sum('total_incoming');
        $totalOutgoing = $cashflowSummary->sum('total_outgoing');
        $netCashflow   = $totalIncoming - $totalOutgoing;

        // Default banks
        $defaultReceive = BankAccount::defaultReceive()->get();
        $defaultSend    = BankAccount::defaultSend()->get();

        return view('bank-accounts.index', compact(
            'accounts',
            'cashflowSummary',
            'totalIncoming',
            'totalOutgoing',
            'netCashflow',
            'defaultReceive',
            'defaultSend'
        ));
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

    /** Legacy: set single default */
    public function setDefault(BankAccount $bankAccount)
    {
        try {
            $this->bankAccountService->setDefault($bankAccount);
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('success', "Rekening {$bankAccount->bank_name} dijadikan default.");
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('error', $e->getMessage());
        }
    }

    /** Set as default for RECEIVING payments (AR — from RS/Klinik) */
    public function setDefaultReceive(BankAccount $bankAccount)
    {
        try {
            $this->bankAccountService->setDefaultForReceive($bankAccount);
            $action = $bankAccount->fresh()->default_for_receive ? 'ditambahkan ke' : 'dihapus dari';
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('success', "Rekening {$bankAccount->bank_name} {$action} daftar default penerimaan.");
        } catch (\InvalidArgumentException $e) {
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('error', $e->getMessage());
        }
    }

    /** Set as default for SENDING payments (AP — to Supplier) */
    public function setDefaultSend(BankAccount $bankAccount)
    {
        try {
            $this->bankAccountService->setDefaultForSend($bankAccount);
            $action = $bankAccount->fresh()->default_for_send ? 'ditambahkan ke' : 'dihapus dari';
            return redirect()
                ->route('web.bank-accounts.index')
                ->with('success', "Rekening {$bankAccount->bank_name} {$action} daftar default pengiriman.");
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

    /** Show cashflow detail for a specific bank account */
    public function cashflow(BankAccount $bankAccount, Request $request)
    {
        $period = $request->get('period', 'month'); // month, quarter, year, all
        $type   = $request->get('type', 'all');     // all, incoming, outgoing

        $query = Payment::where('bank_account_id', $bankAccount->id)
            ->with(['allocations.customerInvoice.organization', 'allocations.supplierInvoice.supplier']);

        // Period filter
        match ($period) {
            'month'   => $query->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year),
            'quarter' => $query->where('payment_date', '>=', now()->startOfQuarter()),
            'year'    => $query->whereYear('payment_date', now()->year),
            default   => null,
        };

        // Type filter
        if ($type !== 'all') {
            $query->where('type', $type);
        }

        $payments = $query->latest('payment_date')->paginate(20)->withQueryString();

        $totalIn  = Payment::where('bank_account_id', $bankAccount->id)->where('type', 'incoming')->whereIn('status', ['completed', 'confirmed'])->sum('amount');
        $totalOut = Payment::where('bank_account_id', $bankAccount->id)->where('type', 'outgoing')->whereIn('status', ['completed', 'confirmed'])->sum('amount');

        return view('bank-accounts.cashflow', compact(
            'bankAccount',
            'payments',
            'totalIn',
            'totalOut',
            'period',
            'type'
        ));
    }
}
