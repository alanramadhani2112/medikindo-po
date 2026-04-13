<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\AuditService;
use Illuminate\Http\Request;

class OrganizationWebController extends Controller
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

        $query = Organization::orderBy('name');

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

        $organizations = $query->paginate(20)->withQueryString();

        // Calculate counts for tab badges
        $counts = [
            'all'      => Organization::count(),
            'active'   => Organization::where('is_active', true)->count(),
            'inactive' => Organization::where('is_active', false)->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Cabang']
        ];

        return view('organizations.index', compact('organizations', 'counts', 'breadcrumbs'));
    }

    public function create()
    {
        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Cabang', 'url' => route('web.organizations.index')],
            ['label' => 'Tambah Cabang']
        ];
        return view('organizations.create', compact('breadcrumbs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'string', 'in:clinic,hospital'],
            'code'           => ['required', 'string', 'max:20', 'unique:organizations,code'],
            'email'          => ['nullable', 'email'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'address'        => ['nullable', 'string'],
            'license_number' => ['nullable', 'string', 'max:100'],
        ]);
        $organization = Organization::create($data);

        $this->auditService->log('create', 'Organization', $organization->id, $organization->toArray());

        return redirect()->route('web.organizations.index')->with('success', 'Organisasi berhasil ditambahkan.');
    }

    public function edit(Organization $organization)
    {
        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Cabang', 'url' => route('web.organizations.index')],
            ['label' => 'Edit Cabang', 'url' => 'javascript:void(0)'],
            ['label' => $organization->name]
        ];
        return view('organizations.edit', compact('organization', 'breadcrumbs'));
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'type'           => ['required', 'string', 'in:clinic,hospital'],
            'code'           => ['required', 'string', 'max:20', 'unique:organizations,code,' . $organization->id],
            'email'          => ['nullable', 'email'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'address'        => ['nullable', 'string'],
            'license_number' => ['nullable', 'string', 'max:100'],
        ]);

        $oldData = $organization->toArray();
        $organization->update($data);
        $changes = $organization->getChanges();

        $this->auditService->log('update', 'Organization', $organization->id, [
            'old'     => $oldData,
            'changes' => $changes
        ]);

        return redirect()->route('web.organizations.index')->with('success', 'Data organisasi berhasil diperbarui.');
    }

    public function destroy(Organization $organization)
    {
        $organization->update(['is_active' => false]);

        $this->auditService->log('deactivate', 'Organization', $organization->id);

        return redirect()->route('web.organizations.index')->with('success', 'Organisasi dinonaktifkan.');
    }

    public function toggleStatus(Organization $organization)
    {
        $organization->update(['is_active' => !$organization->is_active]);
        $statusLabel = $organization->is_active ? 'diaktifkan' : 'dinonaktifkan';

        $this->auditService->log('toggle_status', 'Organization', $organization->id, [
            'is_active' => $organization->is_active
        ]);

        return redirect()->back()->with('success', "Data organisasi berhasil {$statusLabel}.");
    }
}
