<x-index-layout title="AR Aging Dashboard" description="Klasifikasi piutang berdasarkan umur tagihan" :breadcrumbs="[['label' => 'AR Aging']]">
    
    <x-slot name="actions">
        <span class="text-muted fs-7">Per tanggal: <strong>{{ now()->format('d M Y') }}</strong></span>
    </x-slot>

    <x-slot name="top">
        <div class="row g-5 mb-7">
            {{-- Current (0-30 days) --}}
            <div class="col-md-4">
                <div class="card bg-success">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">CURRENT (0-30 HARI)</span>
                        <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($totals['current'], 0, ',', '.') }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">{{ $buckets['current']->count() }} invoice outstanding</div>
                    </div>
                </div>
            </div>

            {{-- Warning (31-60 days) --}}
            <div class="col-md-4">
                <div class="card bg-warning">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">WARNING (31-60 HARI)</span>
                        <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($totals['warning'], 0, ',', '.') }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">{{ $buckets['warning']->count() }} invoice outstanding</div>
                    </div>
                </div>
            </div>

            {{-- Overdue (>60 days) --}}
            <div class="col-md-4">
                <div class="card bg-danger">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">OVERDUE (>60 HARI)</span>
                        <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($totals['overdue'], 0, ',', '.') }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">{{ $buckets['overdue']->count() }} invoice outstanding</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card bg-dark mb-7">
            <div class="card-body py-4 d-flex align-items-center justify-content-between">
                <span class="text-gray-400 fs-6 fw-bold">TOTAL PIUTANG OUTSTANDING</span>
                <span class="text-white fs-2x fw-bold">Rp {{ number_format(array_sum($totals), 0, ',', '.') }}</span>
            </div>
        </div>
    </x-slot>

    <x-slot name="content">
        {{-- Current Bucket Table --}}
        @if($buckets['current']->isNotEmpty())
            <div class="card card-flush mb-7">
                <div class="card-header pt-5 border-start border-success border-4">
                    <h3 class="card-title fw-bold text-success">
                        Current (0–30 Hari)
                        <span class="badge badge-light-success ms-2">{{ $buckets['current']->count() }}</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        @include('ar-aging._bucket_table', ['invoices' => $buckets['current'], 'badgeClass' => 'badge-light-success'])
                    </div>
                </div>
            </div>
        @endif

        {{-- Warning Bucket Table --}}
        @if($buckets['warning']->isNotEmpty())
            <div class="card card-flush mb-7">
                <div class="card-header pt-5 border-start border-warning border-4">
                    <h3 class="card-title fw-bold text-warning">
                        Warning (31–60 Hari)
                        <span class="badge badge-light-warning ms-2">{{ $buckets['warning']->count() }}</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        @include('ar-aging._bucket_table', ['invoices' => $buckets['warning'], 'badgeClass' => 'badge-light-warning'])
                    </div>
                </div>
            </div>
        @endif

        {{-- Overdue Bucket Table --}}
        @if($buckets['overdue']->isNotEmpty())
            <div class="card card-flush mb-7">
                <div class="card-header pt-5 border-start border-danger border-4">
                    <h3 class="card-title fw-bold text-danger">
                        Overdue (&gt;60 Hari)
                        <span class="badge badge-light-danger ms-2">{{ $buckets['overdue']->count() }}</span>
                    </h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        @include('ar-aging._bucket_table', ['invoices' => $buckets['overdue'], 'badgeClass' => 'badge-light-danger'])
                    </div>
                </div>
            </div>
        @endif

        @if($buckets['current']->isEmpty() && $buckets['warning']->isEmpty() && $buckets['overdue']->isEmpty())
            <div class="card card-flush">
                <div class="card-body text-center py-15">
                    <x-empty-state icon="check-circle" title="Tidak Ada Piutang Outstanding" message="Semua invoice sudah lunas atau dibatalkan." />
                </div>
            </div>
        @endif
    </x-slot>
</x-index-layout>
