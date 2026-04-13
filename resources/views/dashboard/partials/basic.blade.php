{{-- BASIC DASHBOARD (Fallback) --}}

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-7">
    <div>
        <h1 class="fs-2hx fw-bold text-gray-900 mb-2">Dashboard</h1>
        <p class="text-gray-600 fs-6 mb-0">Selamat datang, {{ auth()->user()->name }}</p>
    </div>
</div>

{{-- Welcome Card --}}
<div class="row g-5 g-xl-8">
    <div class="col-12">
        <div class="card card-flush">
            <div class="card-body text-center py-20">
                <i class="ki-outline ki-home fs-5x text-primary mb-5"></i>
                <h2 class="fw-bold text-gray-900 mb-3">Selamat Datang di Medikindo Procurement System</h2>
                <p class="text-gray-600 fs-5 mb-7">
                    Anda telah berhasil login ke sistem. Gunakan menu navigasi di sebelah kiri untuk mengakses fitur yang tersedia.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('web.po.index') }}" class="btn btn-primary">
                        <i class="ki-outline ki-document fs-2"></i>
                        Purchase Orders
                    </a>
                    @can('view_invoices')
                    <a href="{{ route('web.invoices.index') }}" class="btn btn-light-primary">
                        <i class="ki-outline ki-bill fs-2"></i>
                        Invoices
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
