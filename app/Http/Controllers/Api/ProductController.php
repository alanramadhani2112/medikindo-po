<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_product|full_access', except: ['index', 'show']),
        ];
    }

    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with('supplier:id,name,code')
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->has('narcotic'), fn($q) => $q->where('is_narcotic', true))
            ->when($request->has('active'), fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(20);

        return response()->json($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return response()->json([
            'message' => 'Product created.',
            'product' => $product->load('supplier'),
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json(['product' => $product->load('supplier')]);
    }

    public function update(StoreProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json(['message' => 'Product updated.', 'product' => $product]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->update(['is_active' => false]);

        return response()->json(['message' => 'Product deactivated.']);
    }
}
