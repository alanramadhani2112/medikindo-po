<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SupplierWebController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('search');

        $query = Supplier::orderBy('name');

        // Status Filtering
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        // Search Implementation
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(20)->withQueryString();

        // Calculate counts for tab badges
        $counts = [
            'all'      => Supplier::count(),
            'active'   => Supplier::where('is_active', true)->count(),
            'inactive' => Supplier::where('is_active', false)->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Supplier']
        ];

        return view('suppliers.index', compact('suppliers', 'counts', 'breadcrumbs'));
    }

    public function create()
    {
        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Supplier', 'url' => route('web.suppliers.index')],
            ['label' => 'Tambah Supplier']
        ];
        return view('suppliers.create', compact('breadcrumbs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'code'                   => ['required', 'string', 'max:20', 'unique:suppliers,code'],
            'address'                => ['nullable', 'string'],
            'phone'                  => ['nullable', 'string', 'max:20'],
            'email'                  => ['nullable', 'email'],
            'npwp'                   => ['nullable', 'string', 'max:30'],
            'license_number'         => ['required', 'string', 'max:100', 'unique:suppliers,license_number'],
            'license_expiry_date'    => ['nullable', 'date', 'after:today'],
            'is_authorized_narcotic' => ['nullable', 'boolean'],
        ]);
        
        $data['is_authorized_narcotic'] = $request->boolean('is_authorized_narcotic');
        
        $supplier = Supplier::create($data);

        $this->auditService->log('create', 'Supplier', $supplier->id, $supplier->toArray());

        return redirect()->route('web.suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Supplier', 'url' => route('web.suppliers.index')],
            ['label' => 'Edit Supplier', 'url' => 'javascript:void(0)'],
            ['label' => $supplier->name]
        ];
        return view('suppliers.edit', compact('supplier', 'breadcrumbs'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:255'],
            'code'                   => ['required', 'string', 'max:20', 'unique:suppliers,code,' . $supplier->id],
            'address'                => ['nullable', 'string'],
            'phone'                  => ['nullable', 'string', 'max:20'],
            'email'                  => ['nullable', 'email'],
            'npwp'                   => ['nullable', 'string', 'max:30'],
            'license_number'         => ['required', 'string', 'max:100', 'unique:suppliers,license_number,' . $supplier->id],
            'license_expiry_date'    => ['nullable', 'date', 'after:today'],
            'is_authorized_narcotic' => ['nullable', 'boolean'],
        ]);

        $data['is_authorized_narcotic'] = $request->boolean('is_authorized_narcotic');

        $oldData = $supplier->toArray();
        $supplier->update($data);
        $changes = $supplier->getChanges();

        $this->auditService->log('update', 'Supplier', $supplier->id, [
            'old'     => $oldData,
            'changes' => $changes
        ]);

        return redirect()->route('web.suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->update(['is_active' => false]);
        
        $this->auditService->log('deactivate', 'Supplier', $supplier->id);

        return redirect()->route('web.suppliers.index')->with('success', 'Supplier dinonaktifkan.');
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        $statusLabel = $supplier->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $this->auditService->log('toggle_status', 'Supplier', $supplier->id, [
            'is_active' => $supplier->is_active
        ]);

        return redirect()->back()->with('success', "Supplier berhasil {$statusLabel}.");
    }
}
