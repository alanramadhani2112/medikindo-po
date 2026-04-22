<x-index-layout title="Bukti Pembayaran" :breadcrumbs="$breadcrumbs">
    <x-slot name="actions">
        @can('create', App\Models\PaymentProof::class)
            <x-button :href="route('web.payment-proofs.create')" icon="plus" label="Submit Bukti Bayar" />
        @endcan
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.payment-proofs.index')">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="Cari No. Invoice atau Bank Ref..." value="{{ request('search') }}">
                </div>
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                'all'         => ['label' => 'Semua',              'icon' => 'ki-home'],
                'submitted'   => ['label' => 'Menunggu Tinjauan',  'icon' => 'ki-file-up'],
                'resubmitted' => ['label' => 'Diajukan Ulang',     'icon' => 'ki-arrows-circle'],
                'approved'    => ['label' => 'Disetujui',          'icon' => 'ki-check-circle'],
                'rejected'    => ['label' => 'Ditolak',            'icon' => 'ki-cross-circle'],
            ];

            // Finance/Admin/Super Admin juga lihat tab Sudah Diverifikasi
            if (auth()->user()->hasAnyRole(['Finance', 'Super Admin', 'Admin Pusat'])) {
                $tabOptions = array_slice($tabOptions, 0, 3, true)
                    + ['verified' => ['label' => 'Sudah Diverifikasi', 'icon' => 'ki-shield-search']]
                    + array_slice($tabOptions, 3, null, true);
            }
        @endphp
        @foreach($tabOptions as $statusKey => $tabData)
            @php $isActive = ($tab === $statusKey) || ($statusKey === 'all' && $tab === ''); @endphp
            <li class="nav-item">
                <a class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}"
                   href="{{ route('web.payment-proofs.index', ['tab' => $statusKey === 'all' ? '' : $statusKey]) }}">
                    <i class="ki-outline ki-{{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                    @if($statusKey !== 'all')
                        <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                            {{ $stats[$statusKey] ?? 0 }}
                        </span>
                    @endif
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Daftar Bukti Pembayaran</x-slot>

    {{-- Main Content --}}
    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>No. Invoice</th>
                <th>RS/Pelanggan</th>
                <th>Tgl Bayar</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paymentProofs as $item)
                <tr>
                    <td>
                        <span class="fw-bold text-gray-800">{{ $item->customerInvoice->invoice_number }}</span>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-gray-800">{{ $item->customerInvoice->organization->name }}</span>
                            <span class="text-muted fs-7">Submitted by: {{ $item->submittedBy->name }}</span>
                        </div>
                    </td>
                    <td>{{ $item->payment_date->format('d M Y') }}</td>
                    <td><span class="fw-bold">Rp {{ number_format($item->amount, 0, ',', '.') }}</span></td>
                    <td>
                        <x-badge :variant="$item->status->color()">{{ $item->status->label() }}</x-badge>
                    </td>
                    <td class="text-end">
                        <x-table-action>
                            <x-table-action.item :href="route('web.payment-proofs.show', $item)" icon="eye" label="Lihat Detail" />
                            @can('verify', $item)
                                @if($item->canBeVerified())
                                    <x-table-action.divider />
                                    <x-table-action.item :href="route('web.payment-proofs.verify', $item)" icon="shield-search" label="Verifikasi & Setujui" color="success" />
                                @endif
                            @endcan
                        </x-table-action>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-10">
                        <x-empty-state icon="file-deleted" title="Tidak Ada Data" message="Tidak ada bukti pembayaran ditemukan untuk filter ini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($paymentProofs->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $paymentProofs->links() }}
        </div>
    @endif
</x-index-layout>
