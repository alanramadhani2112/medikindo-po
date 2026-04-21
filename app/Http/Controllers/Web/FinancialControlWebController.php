<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\CreditLimit;
use Illuminate\Http\Request;

class FinancialControlWebController extends Controller
{
    public function index(Request $request)
    {
        // Check permission instead of hardcoded role
        if (! $request->user()->can('view_credit_control')) {
            return redirect()->route('web.dashboard')->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk mengakses kontrol kredit.');
        }
        
        $organizations = Organization::doesntHave('creditLimit')->orderBy('name')->get();
        
        $limits = CreditLimit::with(['organization.customerInvoices'])
            ->orderBy('is_active', 'desc')
            ->get()
            ->each(function ($limit) {
                // Outstanding AR = sum of (total - paid) for issued/partial_paid invoices
                $limit->total_active_ar = $limit->organization?->customerInvoices
                    ->whereIn('status', [
                        \App\Enums\CustomerInvoiceStatus::ISSUED->value,
                        \App\Enums\CustomerInvoiceStatus::PARTIAL_PAID->value,
                    ])
                    ->sum(fn($inv) => $inv->outstanding_amount) ?? 0;
            });
            
        return view('financial-controls.index', compact('organizations', 'limits'));
    }

    public function store(Request $request)
    {
        if (! $request->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $data = $request->validate([
            'organization_id' => 'required|exists:organizations,id|unique:credit_limits,organization_id',
            'max_limit'       => 'required|numeric|min:0',
            'is_active'       => 'boolean'
        ]);

        CreditLimit::create([
            'organization_id' => $data['organization_id'],
            'max_limit'       => $data['max_limit'],
            'is_active'       => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Proteksi Limit Kredit baru berhasil untuk dikonfigurasi.');
    }

    public function update(Request $request, CreditLimit $financial_control)
    {
        if (! $request->user()->hasRole('Super Admin')) {
            abort(403);
        }

        $data = $request->validate([
            'max_limit' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $financial_control->update([
            'max_limit' => $data['max_limit'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Aturan limit kredit berhasil diperbarui.');
    }
}
