<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Services\ApprovalService;
use Illuminate\Http\Request;

class ApprovalWebController extends Controller
{
    public function __construct(private readonly ApprovalService $approvalService) {}

    public function index(Request $request)
    {
        $user   = $request->user();
        $tab    = $request->get('tab', 'pending');
        $search = $request->get('search');

        // OrganizationScope will automatically filter by organization for non-approver users
        $query = PurchaseOrder::with(['organization', 'supplier', 'creator', 'approvals.approver']);

        if ($tab === 'history') {
            $query->whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_REJECTED]);
        } else {
            $query->where('status', PurchaseOrder::STATUS_SUBMITTED);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        $pendingApprovals = $query->latest()->paginate(10)->withQueryString();

        // Calculate counts
        // OrganizationScope will automatically filter by organization for non-approver users
        $counts = [
            'pending' => PurchaseOrder::where('status', PurchaseOrder::STATUS_SUBMITTED)->count(),
            'history' => PurchaseOrder::whereIn('status', [PurchaseOrder::STATUS_APPROVED, PurchaseOrder::STATUS_REJECTED])->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Logistics & Operations', 'url' => 'javascript:void(0)'],
            ['label' => 'Persetujuan']
        ];

        return view('approvals.index', compact('pendingApprovals', 'counts', 'tab', 'breadcrumbs'));
    }

    public function process(Request $request, PurchaseOrder $purchaseOrder)
    {
        $data = $request->validate([
            'level'    => ['required', 'integer', 'in:1,2'],
            'decision' => ['required', 'in:approved,rejected'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->approvalService->process(
                $purchaseOrder,
                $request->user(),
                $data['level'],
                $data['decision'],
                $data['notes'] ?? null,
            );

            $msg = $data['decision'] === 'approved'
                ? "PO #{$purchaseOrder->po_number} berhasil disetujui."
                : "PO #{$purchaseOrder->po_number} ditolak.";

            return redirect()->route('web.approvals.index')->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
