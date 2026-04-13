<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class OrganizationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_organization|full_access', except: ['index', 'show']),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $organizations = Organization::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->has('active'), fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(20);

        return response()->json($organizations);
    }

    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        $organization = Organization::create($request->validated());

        return response()->json([
            'message'      => 'Organization created.',
            'organization' => $organization,
        ], 201);
    }

    public function show(Organization $organization): JsonResponse
    {
        return response()->json(['organization' => $organization->load('users')]);
    }

    public function update(StoreOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $organization->update($request->validated());

        return response()->json(['message' => 'Organization updated.', 'organization' => $organization]);
    }

    public function destroy(Organization $organization): JsonResponse
    {
        $organization->update(['is_active' => false]);

        return response()->json(['message' => 'Organization deactivated.']);
    }
}
