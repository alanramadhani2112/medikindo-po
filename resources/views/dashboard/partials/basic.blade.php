{{-- BASIC DASHBOARD (Fallback) --}}

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Dashboard</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
</div>

{{-- Welcome Card --}}
<div class="row g-5 g-xl-8 mb-7">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-body text-center py-10">
                <i class="ki-outline ki-check-circle fs-5x text-primary mb-5"></i>
                <h2 class="fw-bold text-gray-900 mb-3">Selamat Datang di PT. Mentari Medika Indonesia</h2>
                <p class="text-gray-600 fs-5 mb-0">
                    Gunakan menu navigasi di sebelah kiri atau aksi cepat di bawah untuk mengakses fitur yang tersedia.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Quick Menu berdasarkan permission --}}
<div class="row g-5 g-xl-8">
    @can('view_purchase_orders')
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('web.po.index') }}" class="card card-flush card-hover h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-6">
                <div class="d-flex align-items-center justify-content-center bg-light-primary rounded" style="width:60px;height:60px;flex-shrink:0;">
                    <i class="ki-outline ki-purchase fs-2x text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5 text-gray-900">Purchase Orders</div>
                    <div class="text-muted fs-7">Kelola pengadaan barang</div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('view_approvals')
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('web.approvals.index') }}" class="card card-flush card-hover h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-6">
                <div class="d-flex align-items-center justify-content-center bg-light-warning rounded" style="width:60px;height:60px;flex-shrink:0;">
                    <i class="ki-outline ki-briefcase fs-2x text-warning"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5 text-gray-900">Approvals</div>
                    <div class="text-muted fs-7">Persetujuan purchase order</div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('view_goods_receipt')
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('web.goods-receipts.index') }}" class="card card-flush card-hover h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-6">
                <div class="d-flex align-items-center justify-content-center bg-light-success rounded" style="width:60px;height:60px;flex-shrink:0;">
                    <i class="ki-outline ki-courier-express fs-2x text-success"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5 text-gray-900">Goods Receipt</div>
                    <div class="text-muted fs-7">Penerimaan barang</div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('view_invoices')
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('web.invoices.customer.index') }}" class="card card-flush card-hover h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-6">
                <div class="d-flex align-items-center justify-content-center bg-light-info rounded" style="width:60px;height:60px;flex-shrink:0;">
                    <i class="ki-outline ki-bill fs-2x text-info"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5 text-gray-900">Invoices</div>
                    <div class="text-muted fs-7">Tagihan & faktur</div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('view_payment_status')
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('web.payment-proofs.index') }}" class="card card-flush card-hover h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-6">
                <div class="d-flex align-items-center justify-content-center bg-light-danger rounded" style="width:60px;height:60px;flex-shrink:0;">
                    <i class="ki-outline ki-shield-tick fs-2x text-danger"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5 text-gray-900">Payment Proofs</div>
                    <div class="text-muted fs-7">Bukti pembayaran</div>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('view_inventory')
    <div class="col-12 col-md-6 col-xl-4">
        <a href="{{ route('inventory.index') }}" class="card card-flush card-hover h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-6">
                <div class="d-flex align-items-center justify-content-center bg-light-dark rounded" style="width:60px;height:60px;flex-shrink:0;">
                    <i class="ki-outline ki-package fs-2x text-dark"></i>
                </div>
                <div>
                    <div class="fw-bold fs-5 text-gray-900">Inventory</div>
                    <div class="text-muted fs-7">Stok & batch produk</div>
                </div>
            </div>
        </a>
    </div>
    @endcan
</div>
