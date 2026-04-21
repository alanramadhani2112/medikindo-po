<x-index-layout title="Payment Ledger" :breadcrumbs="[['label' => 'Payments']]">
    
    <x-slot name="top">
        <div class="row g-5">
            <div class="col-md-4">
                <div class="card bg-success">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">Total Kas Masuk</span>
                        <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($totalIn ?? 0, 0, ',', '.') }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">
                            <i class="ki-outline ki-arrow-down fs-7 me-1"></i>
                            Uang yang diterima dari customer
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">Total Kas Keluar</span>
                        <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($totalOut ?? 0, 0, ',', '.') }}</div>
                        <div class="text-white opacity-75 fs-8 mt-2">
                            <i class="ki-outline ki-arrow-up fs-7 me-1"></i>
                            Uang yang dibayarkan ke supplier
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary">
                    <div class="card-body">
                        <span class="text-white fs-7 fw-bold">Saldo Netto</span>
                        <div class="text-white fs-2x fw-bold mt-2">
                            @php
                                $netBalance = ($totalIn ?? 0) - ($totalOut ?? 0);
                            @endphp
                            <span class="text-white">
                                Rp {{ number_format($netBalance, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="text-white opacity-75 fs-8 mt-2">
                            <i class="ki-outline ki-calculator fs-7 me-1"></i>
                            Selisih kas masuk - kas keluar
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="toolbar">
        <x-filter-bar :action="route('web.payments.index')">
            <input type="hidden" name="tab" value="{{ $tab ?? 'all' }}">
            <div class="flex-grow-1" style="max-width: 400px;">
                <div class="position-relative">
                    <i class="ki-outline ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-4"></i>
                    <input type="text" name="search" class="form-control form-control-solid ps-12" placeholder="Cari deskripsi atau referensi..." value="{{ request('search') }}">
                </div>
            </div>
            <div style="min-width: 150px;">
                <select name="type" class="form-select form-select-solid">
                    <option value="">Semua Tipe</option>
                    <option value="incoming" {{ request('type') === 'incoming' ? 'selected' : '' }}>Incoming</option>
                    <option value="outgoing" {{ request('type') === 'outgoing' ? 'selected' : '' }}>Outgoing</option>
                </select>
            </div>
            <div style="max-width: 150px;">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-solid">
            </div>
            <div style="max-width: 150px;">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-solid">
            </div>
        </x-filter-bar>
    </x-slot>

    <x-slot name="tabs">
        @php
            $tabOptions = [
                'all' => ['label' => 'Semua Transaksi', 'icon' => 'ki-home'],
                'incoming' => ['label' => 'Kas Masuk', 'icon' => 'ki-arrow-down'],
                'outgoing' => ['label' => 'Kas Keluar', 'icon' => 'ki-arrow-up'],
                'pending' => ['label' => 'Pending', 'icon' => 'ki-time'],
                'confirmed' => ['label' => 'Confirmed', 'icon' => 'ki-check-circle'],
            ];
        @endphp
        @foreach($tabOptions as $val => $tabData)
            @php 
                $isActive = $tab === $val;
                $count = $counts[$val] ?? 0;
            @endphp
            <li class="nav-item">
                <a href="{{ route('web.payments.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val])) }}" 
                   class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                    <i class="ki-outline {{ $tabData['icon'] }} fs-4 me-3"></i>
                    <span class="fs-6 fw-bold me-3">{{ $tabData['label'] }}</span>
                    <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-2">
                        {{ $count }}
                    </span>
                </a>
            </li>
        @endforeach
    </x-slot>

    <x-slot name="tableHeader">Riwayat Transaksi</x-slot>

    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
        <thead>
            <tr class="fw-bold text-muted">
                <th>Payment ID</th>
                <th>Tanggal</th>
                <th>Deskripsi / Referensi</th>
                <th>Metode</th>
                <th>Tipe</th>
                <th class="text-end">Amount</th>
                <th>Status</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments ?? [] as $payment)
                <tr>
                    <td>
                        <span class="text-gray-900 fw-bold fs-6">{{ $payment->payment_number ?? 'PAY-' . $payment->id }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-gray-800 fs-6">{{ $payment->payment_date->format('d/m/Y') }}</div>
                        <div class="text-muted fs-7">{{ $payment->payment_date->format('H:i') }}</div>
                    </td>
                    <td>
                        <div class="fw-bold text-gray-800 fs-6 mb-1">{{ $payment->description ?? 'Tanpa deskripsi' }}</div>
                        <div class="text-muted fs-7">
                            <i class="ki-outline ki-document fs-8 me-1"></i>
                            Ref: {{ $payment->reference_number ?? '—' }}
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-light-info">{{ strtoupper($payment->payment_method) }}</span>
                    </td>
                    <td>
                        @php
                            $typeColor = $payment->type === 'incoming' ? 'success' : 'danger';
                        @endphp
                        <span class="badge badge-light-{{ $typeColor }} fw-bold">{{ strtoupper($payment->type) }}</span>
                    </td>
                    <td class="text-end">
                        <span class="fw-bold fs-6 {{ $payment->type === 'incoming' ? 'text-success' : 'text-danger' }}">
                            {{ $payment->type === 'incoming' ? '+' : '-' }} Rp {{ number_format($payment->amount, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusColor = match($payment->status ?? 'confirmed') {
                                'confirmed' => 'success',
                                'pending' => 'warning',
                                'cancelled' => 'danger',
                                default => 'primary'
                            };
                        @endphp
                        <span class="badge badge-light-{{ $statusColor }} fw-bold">{{ strtoupper($payment->status ?? 'CONFIRMED') }}</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('web.payments.show', $payment) }}" class="btn btn-icon btn-light-primary btn-sm">
                            <i class="ki-outline ki-eye fs-2"></i>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-10">
                        <x-empty-state icon="entrance-right" title="Belum Ada Transaksi" message="Transaksi pembayaran akan muncul setelah proses penerimaan atau pengeluaran tercatat." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if(isset($payments) && $payments->hasPages())
        <div class="d-flex justify-content-end mt-5">
            {{ $payments->links() }}
        </div>
    @endif
</x-index-layout>
