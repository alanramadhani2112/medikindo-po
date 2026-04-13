<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SupplierController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_supplier|full_access', except: ['index', 'show']),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::query()
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->has('active'), fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(20);

        return response()->json($suppliers);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());

        return response()->json([
            'message'  => 'Supplier created.',
            'supplier' => $supplier,
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json(['supplier' => $supplier->load('products')]);
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());

        return response()->json(['message' => 'Supplier updated.', 'supplier' => $supplier]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->update(['is_active' => false]);

        return response()->json(['message' => 'Supplier deactivated.']);
    }
}
