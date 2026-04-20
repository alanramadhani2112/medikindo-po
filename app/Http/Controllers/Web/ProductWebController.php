<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::with('supplier')
            ->where('is_active', true)
            ->when($request->search, function($q, $s) {
                return $q->where(function($sub) use ($s) {
                    $sub->where('name', 'like', "%$s%")->orWhere('sku', 'like', "%$s%");
                });
            })
            ->when($request->supplier_id, fn($q, $id) => $q->where('supplier_id', $id))
            ->when($request->type, function($q, $type) {
                if ($type === 'narcotic') return $q->where('is_narcotic', true);
                if ($type === 'non-narcotic') return $q->where('is_narcotic', false);
                if ($type === 'expiring') return $q->expiringSoon(60);
                if ($type === 'expired') return $q->expired();
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all'          => Product::where('is_active', true)->count(),
            'non-narcotic' => Product::where('is_active', true)->where('is_narcotic', false)->count(),
            'narcotic'     => Product::where('is_active', true)->where('is_narcotic', true)->count(),
            'expiring'     => Product::where('is_active', true)->expiringSoon(60)->count(),
            'expired'      => Product::where('is_active', true)->expired()->count(),
        ];

        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Katalog Produk']
        ];

        return view('products.index', compact('products', 'suppliers', 'counts', 'breadcrumbs'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $categories = Product::CATEGORIES;
        $units = Product::UNITS;
        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Katalog Produk', 'url' => route('web.products.index')],
            ['label' => 'Tambah Baru']
        ];
        return view('products.create', compact('suppliers', 'categories', 'units', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $rules = [
            'supplier_id'         => ['required', 'exists:suppliers,id'],
            'name'                => ['required', 'string', 'max:255'],
            'sku'                 => ['required', 'string', 'max:50', 'unique:products,sku'],
            'unit'                => ['required', 'string', 'max:30'],
            'price'               => ['nullable', 'numeric', 'min:0'],
            'cost_price'          => ['required', 'numeric', 'min:0'],
            'selling_price'       => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0'],
            'category'            => ['nullable', 'string', 'max:100'],
            'description'         => ['nullable', 'string'],
            'is_narcotic'         => ['nullable', 'boolean'],
            'expiry_date'         => ['nullable', 'date', 'after:today'],
            'batch_no'            => ['nullable', 'string', 'max:100'],
        ];

        // Conditional validation: if is_narcotic = true, narcotic_group is REQUIRED
        if ($request->boolean('is_narcotic')) {
            $rules['narcotic_group'] = ['required', 'in:I,II,III'];
        }

        $data = $request->validate($rules);
        
        $data['is_narcotic'] = $request->boolean('is_narcotic');
        $data['discount_percentage'] = $data['discount_percentage'] ?? 0;
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        
        // Auto-set requires_sp and requires_prescription if narcotic
        if ($data['is_narcotic']) {
            $data['requires_sp'] = true;
            $data['requires_prescription'] = true;
        } else {
            $data['requires_sp'] = false;
            $data['requires_prescription'] = false;
            $data['narcotic_group'] = null;
        }
        
        $product = Product::create($data);

        $this->auditService->log('create', 'Product', $product->id, $product->toArray());

        return redirect()->route('web.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $categories = Product::CATEGORIES;
        $units = Product::UNITS;
        $breadcrumbs = [
            ['label' => 'Master Data', 'url' => 'javascript:void(0)'],
            ['label' => 'Katalog Produk', 'url' => route('web.products.index')],
            ['label' => 'Edit Produk', 'url' => 'javascript:void(0)'],
            ['label' => $product->name]
        ];
        return view('products.edit', compact('product', 'suppliers', 'categories', 'units', 'breadcrumbs'));
    }

    public function update(Request $request, Product $product)
    {
        $rules = [
            'supplier_id'         => ['required', 'exists:suppliers,id'],
            'name'                => ['required', 'string', 'max:255'],
            'sku'                 => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'unit'                => ['required', 'string', 'max:30'],
            'price'               => ['nullable', 'numeric', 'min:0'],
            'cost_price'          => ['required', 'numeric', 'min:0'],
            'selling_price'       => ['required', 'numeric', 'min:0'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0'],
            'category'            => ['nullable', 'string', 'max:100'],
            'description'         => ['nullable', 'string'],
            'is_narcotic'         => ['nullable', 'boolean'],
            'expiry_date'         => ['nullable', 'date', 'after:today'],
            'batch_no'            => ['nullable', 'string', 'max:100'],
        ];

        // Conditional validation: if is_narcotic = true, narcotic_group is REQUIRED
        if ($request->boolean('is_narcotic')) {
            $rules['narcotic_group'] = ['required', 'in:I,II,III'];
        }

        $data = $request->validate($rules);

        $data['is_narcotic'] = $request->boolean('is_narcotic');
        $data['discount_percentage'] = $data['discount_percentage'] ?? 0;
        $data['discount_amount'] = $data['discount_amount'] ?? 0;
        
        // Auto-set requires_sp and requires_prescription if narcotic
        if ($data['is_narcotic']) {
            $data['requires_sp'] = true;
            $data['requires_prescription'] = true;
        } else {
            $data['requires_sp'] = false;
            $data['requires_prescription'] = false;
            $data['narcotic_group'] = null;
        }
        
        $oldData = $product->toArray();
        $product->update($data);
        $changes = $product->getChanges();

        $this->auditService->log('update', 'Product', $product->id, [
            'old'     => $oldData,
            'changes' => $changes
        ]);

        return redirect()->route('web.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->update(['is_active' => false]);

        $this->auditService->log('deactivate', 'Product', $product->id);

        return redirect()->route('web.products.index')->with('success', 'Produk dinonaktifkan.');
    }
}
