<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\PriceList;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriceListWebController extends Controller
{
    /**
     * GET /price-lists
     */
    public function index(Request $request): View
    {
        $query = PriceList::with(['organization', 'product'])
            ->when($request->filled('organization_id'), fn($q) => $q->where('organization_id', $request->organization_id))
            ->when($request->filled('product_id'), fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->filled('is_active'), fn($q) => $q->where('is_active', $request->is_active === '1'));

        $priceLists = $query->latest()->paginate(20)->withQueryString();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('price-lists.index', compact('priceLists', 'organizations', 'products'));
    }

    /**
     * GET /price-lists/create
     */
    public function create(): View
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('price-lists.create', compact('organizations', 'products'));
    }

    /**
     * POST /price-lists
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'product_id'      => 'required|exists:products,id',
            'selling_price'   => 'required|numeric|min:0',
            'effective_date'  => 'required|date',
            'expiry_date'     => 'nullable|date|after:effective_date',
            'is_active'       => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        PriceList::create($validated);

        return redirect()
            ->route('web.price-lists.index')
            ->with('success', 'Harga jual berhasil ditambahkan.');
    }

    /**
     * GET /price-lists/{priceList}/edit
     */
    public function edit(PriceList $priceList): View
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('price-lists.edit', compact('priceList', 'organizations', 'products'));
    }

    /**
     * PUT /price-lists/{priceList}
     */
    public function update(Request $request, PriceList $priceList): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'product_id'      => 'required|exists:products,id',
            'selling_price'   => 'required|numeric|min:0',
            'effective_date'  => 'required|date',
            'expiry_date'     => 'nullable|date|after:effective_date',
            'is_active'       => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $priceList->update($validated);

        return redirect()
            ->route('web.price-lists.index')
            ->with('success', 'Harga jual berhasil diperbarui.');
    }

    /**
     * DELETE /price-lists/{priceList}
     * Deactivates instead of hard delete.
     */
    public function destroy(PriceList $priceList): RedirectResponse
    {
        $priceList->update(['is_active' => false]);

        return redirect()
            ->route('web.price-lists.index')
            ->with('success', 'Harga jual berhasil dinonaktifkan.');
    }
}
