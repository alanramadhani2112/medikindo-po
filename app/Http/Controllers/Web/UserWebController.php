<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserWebController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $user   = $request->user();
        $status = $request->get('status');
        $search = $request->get('search');
        $role   = $request->get('role');

        $query = User::with(['roles', 'organization'])
            ->when(! $user->hasRole('Super Admin'), fn($q) => $q->where('organization_id', $user->organization_id))
            ->orderBy('name');

        // Status Filtering
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        // Role Filtering
        if ($role) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        // Search Implementation
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        // Calculate counts for tab badges (respecting organization scope)
        $counts = [
            'all'      => User::when(! $user->hasRole('Super Admin'), fn($q) => $q->where('organization_id', $user->organization_id))->count(),
            'active'   => User::where('is_active', true)->when(! $user->hasRole('Super Admin'), fn($q) => $q->where('organization_id', $user->organization_id))->count(),
            'inactive' => User::where('is_active', false)->when(! $user->hasRole('Super Admin'), fn($q) => $q->where('organization_id', $user->organization_id))->count(),
        ];

        $organizations = Organization::where('is_active', true)->orderBy('name')->get();

        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Pengguna']
        ];

        return view('users.index', compact('users', 'organizations', 'counts', 'breadcrumbs'));
    }

    public function create()
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Pengguna', 'url' => route('web.users.index')],
            ['label' => 'Tambah Pengguna']
        ];
        return view('users.create', compact('organizations', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:8'],
            'role'            => ['required', 'string', 'exists:roles,name'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
        ]);

        $newUser = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'organization_id' => $data['organization_id'] ?? null,
            'is_active'       => true,
        ]);
        $newUser->syncRoles([$data['role']]);

        $this->auditService->log('create', 'User', $newUser->id, [
            'name'  => $newUser->name,
            'email' => $newUser->email,
            'role'  => $data['role']
        ]);

        return redirect()->route('web.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Manajemen Pengguna', 'url' => route('web.users.index')],
            ['label' => 'Edit Pengguna', 'url' => 'javascript:void(0)'],
            ['label' => $user->name]
        ];
        return view('users.edit', compact('user', 'organizations', 'breadcrumbs'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'unique:users,email,' . $user->id],
            'password'        => ['nullable', 'string', 'min:8'],
            'role'            => ['required', 'string', 'exists:roles,name'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'is_active'       => ['nullable', 'boolean'],
        ]);

        $oldData = $user->toArray();
        $oldData['role'] = $user->getRoleNames()->first();

        $user->update([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'organization_id' => $data['organization_id'] ?? null,
            'is_active'       => $request->has('is_active') ? (bool) $data['is_active'] : $user->is_active,
        ]);

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles([$data['role']]);
        
        $changes = $user->getChanges();
        $changes['role'] = $data['role'];

        $this->auditService->log('update', 'User', $user->id, [
            'old'     => $oldData,
            'changes' => $changes
        ]);

        return redirect()->route('web.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('web.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->purchaseOrders()->exists() || $user->approvals()->exists()) {
            $user->update(['is_active' => false]);
            $this->auditService->log('deactivate', 'User', $user->id);
            return redirect()->route('web.users.index')->with('success', 'Pengguna memiliki riwayat transaksi dan telah dinonaktifkan.');
        }

        $userId = $user->id;
        $user->delete();
        $this->auditService->log('delete', 'User', $userId);

        return redirect()->route('web.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
