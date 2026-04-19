<x-index-layout title="Credit Notes" description="Kelola nota kredit untuk retur, diskon, dan koreksi tagihan" :breadcrumbs="$breadcrumbs">
    
    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.credit-notes.index')">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="Cari No. CN atau Alasan..." value="{{ request('search') }}">
                </div>
            </div>
            
            <div style="min-width: 150px;">
                <select name="type" class="form-select form-select-solid">
                    <option value="">Semua Tipe</option>
                    <option value="return" @selected(request('type') === 'return')>Retur Barang</option>
                    <option value="discount" @selected(request('type') === 'discount')>Diskon</option>
                    <option value="correction" @selected(request('type') === 'correction')>Koreksi</option>
                    <option value="cancellation" @selected(request('type') === 'cancellation')>Pembatalan</option>
                </select>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                'all' => ['label' => 'Semua', 'icon' => 'ki-home'],
                'draft' => ['label' => 'Draft', 'icon' => 'ki-pencil'],
                'issued' => ['label' => 'Issued', 'icon' => 'ki-check-circle'],
                'applied' => ['label' => 'Applied', 'icon' => 'ki-verify'],
            ];
            $currentStatus = request('status', 'all');
        @endphp
        @foreach($tabOptions as $val => $tabData)
            @php $isActive = $currentStatus === $val; @endphp
            <li class="nav-item">
                <a href="{{ route('web.credit-notes.index', array_merge(request()->except(['status', 'page']), ['status' => $val === 'all' ? null : $val])) }}" 
                   class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                    <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                    <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                        {{ $counts[$val] }}
                    </span>
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Daftar Credit Note</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>No. CN</th>
                <th>Tipe & Alasan</th>
                <th>Invoice Terkait</th>
                <th class="text-end">Total Amount</th>
                <th class="text-center">Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($creditNotes as $cn)
                <tr>
                    <td>
                        <a href="{{ route('web.credit-notes.show', $cn) }}" class="fw-bold text-gray-900 text-hover-primary">
                            {{ $cn->cn_number }}
                        </a>
                        <div class="text-muted fs-7 mt-1">{{ $cn->created_at->format('d M Y') }}</div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="badge badge-light-primary fw-bold mb-1 w-fit">{{ strtoupper($cn->type) }}</span>
                            <span class="text-gray-700 fs-7 text-truncate" style="max-width: 250px;">{{ $cn->reason }}</span>
                        </div>
                    </td>
                    <td>
                        @if($cn->customerInvoice)
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-7">AR: {{ $cn->customerInvoice->invoice_number }}</span>
                                <span class="text-muted fs-8">{{ $cn->organization?->name }}</span>
                            </div>
                        @elseif($cn->supplierInvoice)
                            <div class="d-flex flex-column">
                                <span class="text-gray-800 fw-bold fs-7">AP: {{ $cn->supplierInvoice->invoice_number }}</span>
                                <span class="text-muted fs-8">{{ $cn->supplierInvoice->supplier?->name }}</span>
                            </div>
                        @else
                            <span class="text-muted fs-7">—</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($cn->total_amount, 0, ',', '.') }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $statusColor = match($cn->status) {
                                'applied' => 'success',
                                'issued' => 'warning',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge badge-light-{{ $statusColor }} fw-bold">{{ strtoupper($cn->status) }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('web.credit-notes.show', $cn) }}" class="btn btn-icon btn-light-primary btn-sm" title="Lihat Detail">
                            <i class="ki-outline ki-eye fs-2"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-10">
                        <x-empty-state icon="document" title="Tidak Ada Data" message="Belum ada credit note yang terdaftar." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($creditNotes->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $creditNotes->links() }}
        </div>
    @endif
</x-index-layout>
