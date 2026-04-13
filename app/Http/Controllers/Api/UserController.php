<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * List users.
     * Super Admin: all users.
     * Organization User: own organization only.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $actor = $request->user();

        $users = User::with(['organization:id,name', 'roles'])
            ->when(! $actor->isSuperAdmin(), fn($q) => $q->where('organization_id', $actor->organization_id))
            ->when($request->organization_id, fn($q, $id) => $q->where('organization_id', $id))
            ->when($request->role, fn($q, $role) => $q->whereRelation('roles', 'name', $role))
            ->when(
                $request->has('active'),
                fn($q) => $q->where('is_active', filter_var($request->active, FILTER_VALIDATE_BOOLEAN))
            )
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Get a single user with roles.
     */
    public function show(User $user): JsonResponse
    {
        Gate::authorize('view', $user);

        return response()->json([
            'user' => $user->load(['organization:id,name', 'roles', 'permissions']),
        ]);
    }

    /**
     * Update user profile, status, or role.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        Gate::authorize('update', $user);

        $data = $request->validated();

        $user->update(array_filter([
            'name'            => $data['name'] ?? null,
            'email'           => $data['email'] ?? null,
            'organization_id' => array_key_exists('organization_id', $data) ? $data['organization_id'] : null,
            'is_active'       => $data['is_active'] ?? null,
        ], fn($v) => $v !== null));

        // Role reassignment — Super Admin only (enforced by request rules)
        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return response()->json([
            'message' => 'User updated.',
            'user'    => $user->fresh(['organization', 'roles']),
        ]);
    }

    /**
     * Deactivate a user (soft delete).
     */
    public function destroy(User $user): JsonResponse
    {
        Gate::authorize('delete', $user);

        $user->update(['is_active' => false]);

        return response()->json(['message' => 'User deactivated.']);
    }
}
