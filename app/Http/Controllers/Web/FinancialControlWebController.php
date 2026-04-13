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
                // Calculate outstanding AR for each organization safely
                $limit->total_active_ar = $limit->organization?->customerInvoices
                    ->whereIn('status', ['unpaid', 'partial'])
                    ->sum(fn($inv) => $inv->total_amount - $inv->paid_amount) ?? 0;
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
