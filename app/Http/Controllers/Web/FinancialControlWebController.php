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
                // Check both enum values and string values for compatibility
                $limit->total_active_ar = $limit->organization?->customerInvoices
                    ->filter(function($inv) {
                        $status = $inv->status instanceof \BackedEnum ? $inv->status->value : $inv->status;
                        return in_array($status, ['issued', 'partial_paid', 'overdue']);
                    })
                    ->sum(fn($inv) => $inv->outstanding_amount ?? ($inv->total_amount - $inv->paid_amount)) ?? 0;
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

        // Get organization to check type
        $organization = Organization::findOrFail($data['organization_id']);
        
        // Define max limits based on organization type
        $maxLimits = [
            'hospital' => 20000000000,  // 20 Miliar
            'rs' => 20000000000,         // 20 Miliar
            'clinic' => 500000000,       // 500 Juta
            'klinik' => 500000000,       // 500 Juta
        ];
        
        $orgType = strtolower($organization->type);
        $maxAllowed = $maxLimits[$orgType] ?? 500000000; // Default: 500 Juta
        
        // Validate max_limit doesn't exceed allowed maximum
        if ($data['max_limit'] > $maxAllowed) {
            return back()->withErrors([
                'max_limit' => "Plafon tidak boleh melebihi Rp " . number_format($maxAllowed, 0, ',', '.') . " untuk tipe organisasi {$organization->type}."
            ])->withInput();
        }

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

        // Get organization to check type
        $organization = $financial_control->organization;
        
        // Define max limits based on organization type
        $maxLimits = [
            'hospital' => 20000000000,  // 20 Miliar
            'rs' => 20000000000,         // 20 Miliar
            'clinic' => 500000000,       // 500 Juta
            'klinik' => 500000000,       // 500 Juta
        ];
        
        $orgType = strtolower($organization->type);
        $maxAllowed = $maxLimits[$orgType] ?? 500000000; // Default: 500 Juta
        
        // Validate max_limit doesn't exceed allowed maximum
        if ($data['max_limit'] > $maxAllowed) {
            return back()->withErrors([
                'max_limit' => "Plafon tidak boleh melebihi Rp " . number_format($maxAllowed, 0, ',', '.') . " untuk tipe organisasi {$organization->type}."
            ])->withInput();
        }

        $financial_control->update([
            'max_limit' => $data['max_limit'],
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Aturan limit kredit berhasil diperbarui.');
    }
}
