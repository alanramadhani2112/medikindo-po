{{-- 
Conversion Metadata:
- Original: Tailwind CSS
- Converted: Bootstrap 5 + Metronic 8
- Date: 2024
- Category: Purchase Orders
- Validated: Pending
--}}
<x-layout title="Detail PO {{ $po->po_number }}">

    {{-- --- 1. HEADER --- --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-4 mb-7">
        <div class="d-flex flex-column gap-2">
            <div class="d-flex align-items-center gap-3">
                <h1 class="fs-2 fw-bold text-gray-900 mb-0">{{ $po->po_number }}</h1>
                @php
                    $badgeClass = match($po->status) {
                        'draft' => 'badge-light-secondary',
                        'submitted' => 'badge-light-warning',
                        'approved' => 'badge-light-primary',
                        'shipped' => 'badge-light-primary',
                        'delivered', 'completed' => 'badge-light-success',
                        'rejected', 'cancelled' => 'badge-light-danger',
                        default => 'badge-light-secondary'
                    };
                @endphp
                <span class="badge {{ $badgeClass }} fw-bold">{{ strtoupper($po->status) }}</span>
            </div>
            <p class="text-gray-600 fs-6 mb-0">Pesanan diterbitkan pada {{ $po->created_at->format('d M Y, H:i') }} oleh {{ $po->creator?->name ?? 'System' }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($po->isDraft())
                @can('submit_po')
                    <form method="POST" action="{{ route('web.po.submit', $po) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-send fs-3"></i>
                            Submit
                        </button>
                    </form>
                @endcan
            @endif

            @if($po->isApproved())
                @can('approve_po')
                    <form method="POST" action="{{ route('web.po.mark_shipped', $po) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-delivery fs-3"></i>
                            Kirim
                        </button>
                    </form>
                @endcan
            @endif

            <button type="button" class="btn btn-light" onclick="window.open('{{ route('web.po.pdf', $po) }}', '_blank')">
                <i class="ki-duotone ki-cloud-download fs-3"></i>
                PDF
            </button>
            <a href="{{ route('web.po.index') }}" class="btn btn-secondary">
                <i class="ki-duotone ki-arrow-left fs-3"></i>
                Kembali
            </a>
        </div>
    </div>

    {{-- --- 2. MAIN GRID (2:1) --- --}}
    <div class="row g-5 g-xl-8">
        {{-- LEFT (2): Main Content --}}
        <div class="col-lg-8">
            <div class="card card-flush mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold fs-3">Rincian Item Pengadaan</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-200px">Produk</th>
                                    <th class="min-w-100px">SKU</th>
                                    <th class="min-w-80px text-end">Qty</th>
                                    <th class="min-w-120px text-end">Harga Satuan</th>
                                    <th class="min-w-120px text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($po->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-gray-900 fw-bold d-block fs-6">{{ $item->product?->name ?? '—' }}</span>
                                                <span class="text-gray-600 fw-semibold fs-7">{{ $item->product?->category ?? 'General' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-600 fw-semibold fs-7 font-monospace">{{ $item->product?->sku ?? '—' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-900 fw-bold d-block fs-6">{{ number_format($item->quantity, 0, ',', '.') }}</span>
                                            <span class="text-gray-600 fw-semibold fs-7">{{ $item->product?->unit ?? 'Unit' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-gray-800 fw-semibold d-block fs-6">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="text-primary fw-bold d-block fs-6">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-light">
                                    <td colspan="4" class="text-end">
                                        <span class="text-gray-700 fw-bold fs-5">Total Nilai Kontrak PO</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-primary fw-bold fs-3">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($po->notes)
                <div class="card card-flush mb-5 mb-xl-8">
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title fw-bold fs-3">Catatan Internal</h3>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-gray-600 fs-6 mb-0 lh-lg">{{ $po->notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- RIGHT (1): Secondary Content --}}
        <div class="col-lg-4">
            <div class="card card-flush bg-light-primary mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold fs-3 text-primary">Informasi Transaksi</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-7">
                        <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Organisasi Pemesan</span>
                        <div class="text-gray-900 fw-bold fs-4 mb-1">{{ $po->organization?->name ?? '—' }}</div>
                        <span class="text-primary fw-semibold fs-7">{{ $po->organization?->type ?? 'Clinic' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-700 fw-semibold fs-7 d-block mb-2">Rekan Supplier</span>
                        <div class="text-gray-900 fw-bold fs-4 mb-1">{{ $po->supplier?->name ?? '—' }}</div>
                        <span class="text-gray-600 fw-semibold fs-7">{{ $po->supplier?->code ?? 'SUP' }}</span>
                    </div>
                </div>
            </div>

            <div class="card card-flush mb-5 mb-xl-8">
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title fw-bold fs-3">Riwayat Approval</h3>
                </div>
                <div class="card-body pt-0">
                    @forelse($po->approvals as $approval)
                        <div class="pb-4 mb-4 border-bottom border-gray-200 {{ $loop->last ? 'border-0 pb-0 mb-0' : '' }}">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-gray-700 fw-semibold fs-7">Level {{ $approval->level }}</span>
                                @php
                                    $approvalBadgeClass = match($approval->status) {
                                        'approved' => 'badge-light-success',
                                        'rejected' => 'badge-light-danger',
                                        default => 'badge-light-warning'
                                    };
                                @endphp
                                <span class="badge {{ $approvalBadgeClass }} fw-bold">
                                    {{ strtoupper($approval->status) }}
                                </span>
                            </div>
                            <div class="text-gray-900 fw-semibold fs-6 mb-1">{{ $approval->approver?->name ?? 'System' }}</div>
                            <p class="text-gray-600 fs-7 mb-0">{{ $approval->actioned_at ? $approval->actioned_at->format('d/m H:i') : 'PENDING' }}</p>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ki-duotone ki-information-5 fs-3x text-gray-400 mb-3"></i>
                                <span class="text-gray-600 fs-5">Belum ada riwayat approval.</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</x-layout>

