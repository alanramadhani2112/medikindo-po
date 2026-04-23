@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">Akun Bank Medikindo</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola rekening bank, tracking cashflow masuk & keluar</p>
        </div>
        @can('manage_bank_accounts')
            <a href="{{ route('web.bank-accounts.create') }}" class="btn btn-primary">
                <i class="ki-outline ki-plus fs-4 me-1"></i>Tambah Rekening
            </a>
        @endcan
    </div>

    {{-- Cashflow Summary Cards --}}
    <div class="row g-5 mb-7">
        <div class="col-md-3">
            <div class="card h-100" style="background: linear-gradient(135deg, #1b4b7f 0%, #153a63 100%);">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="ki-outline ki-bank fs-2x text-white opacity-75"></i>
                        <span class="text-white opacity-75 fs-7 fw-bold">Total Rekening Aktif</span>
                    </div>
                    <div class="text-white fs-2x fw-bold">{{ \App\Models\BankAccount::where('is_active', true)->count() }}</div>
                    <div class="text-white opacity-50 fs-8 mt-1">dari {{ $accounts->total() }} rekening terdaftar</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 bg-light-success border border-success border-dashed">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="ki-outline ki-entrance-right fs-2x text-success"></i>
                        <span class="text-success fs-7 fw-bold">Total Uang Masuk</span>
                    </div>
                    <div class="text-success fs-2x fw-bold">Rp {{ number_format($totalIncoming, 0, ',', '.') }}</div>
                    <div class="text-muted fs-8 mt-1">Dari RS/Klinik (AR)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 bg-light-danger border border-danger border-dashed">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="ki-outline ki-exit-right fs-2x text-danger"></i>
                        <span class="text-danger fs-7 fw-bold">Total Uang Keluar</span>
                    </div>
                    <div class="text-danger fs-2x fw-bold">Rp {{ number_format($totalOutgoing, 0, ',', '.') }}</div>
                    <div class="text-muted fs-8 mt-1">Ke Supplier (AP)</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 {{ $netCashflow >= 0 ? 'bg-light-primary border border-primary' : 'bg-light-warning border border-warning' }} border-dashed">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="ki-outline ki-chart-line-up fs-2x {{ $netCashflow >= 0 ? 'text-primary' : 'text-warning' }}"></i>
                        <span class="{{ $netCashflow >= 0 ? 'text-primary' : 'text-warning' }} fs-7 fw-bold">Net Cashflow</span>
                    </div>
                    <div class="{{ $netCashflow >= 0 ? 'text-primary' : 'text-warning' }} fs-2x fw-bold">
                        {{ $netCashflow >= 0 ? '+' : '' }}Rp {{ number_format($netCashflow, 0, ',', '.') }}
                    </div>
                    <div class="text-muted fs-8 mt-1">Masuk - Keluar</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Default Banks Info --}}
    <div class="row g-5 mb-7">
        {{-- Default Receive --}}
        <div class="col-md-6">
            <div class="card border border-success border-dashed">
                <div class="card-header pt-4 pb-3 bg-light-success">
                    <h3 class="card-title fs-6 fw-bold text-success">
                        <i class="ki-outline ki-entrance-right fs-4 me-2"></i>
                        Default Penerimaan (AR dari RS/Klinik)
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-success fs-9">Max 3 rekening</span>
                    </div>
                </div>
                <div class="card-body py-3">
                    @forelse($defaultReceive as $i => $bank)
                        <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom border-gray-200' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge badge-success fw-bold fs-9">#{{ $i + 1 }}</span>
                                <div>
                                    <div class="fw-bold text-gray-800 fs-7">{{ $bank->bank_name }}</div>
                                    <div class="text-muted fs-9 font-monospace">{{ $bank->account_number }}</div>
                                </div>
                            </div>
                            <span class="badge badge-light-{{ $bank->getAccountTypeBadgeColor() }} fs-9">
                                {{ $bank->getAccountTypeLabel() }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted fs-7">
                            <i class="ki-outline ki-information-5 fs-5 me-1"></i>
                            Belum ada rekening default penerimaan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Default Send --}}
        <div class="col-md-6">
            <div class="card border border-danger border-dashed">
                <div class="card-header pt-4 pb-3 bg-light-danger">
                    <h3 class="card-title fs-6 fw-bold text-danger">
                        <i class="ki-outline ki-exit-right fs-4 me-2"></i>
                        Default Pengiriman (AP ke Supplier)
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-danger fs-9">Max 3 rekening</span>
                    </div>
                </div>
                <div class="card-body py-3">
                    @forelse($defaultSend as $i => $bank)
                        <div class="d-flex align-items-center justify-content-between py-2 {{ !$loop->last ? 'border-bottom border-gray-200' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge badge-danger fw-bold fs-9">#{{ $i + 1 }}</span>
                                <div>
                                    <div class="fw-bold text-gray-800 fs-7">{{ $bank->bank_name }}</div>
                                    <div class="text-muted fs-9 font-monospace">{{ $bank->account_number }}</div>
                                </div>
                            </div>
                            <span class="badge badge-light-{{ $bank->getAccountTypeBadgeColor() }} fs-9">
                                {{ $bank->getAccountTypeLabel() }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted fs-7">
                            <i class="ki-outline ki-information-5 fs-5 me-1"></i>
                            Belum ada rekening default pengiriman
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Cashflow Per Bank --}}
    @if($cashflowSummary->isNotEmpty())
    <div class="card mb-7">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold text-gray-800">
                <i class="ki-outline ki-chart-pie-3 fs-3 me-2 text-primary"></i>
                Cashflow Per Rekening
            </h3>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-200 align-middle gs-0 gy-3 mb-0">
                    <thead>
                        <tr class="fw-bold text-muted fs-8 text-uppercase">
                            <th>Rekening</th>
                            <th>Tipe</th>
                            <th class="text-end text-success">Uang Masuk</th>
                            <th class="text-end text-danger">Uang Keluar</th>
                            <th class="text-end">Net Cashflow</th>
                            <th class="text-end">Saldo Saat Ini</th>
                            <th class="text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cashflowSummary as $row)
                            @php $bank = $accounts->firstWhere('id', $row['id']); @endphp
                            <tr>
                                <td>
                                    <div class="fw-bold text-gray-800 fs-7">{{ $row['bank_name'] }}</div>
                                    <div class="text-muted fs-9 font-monospace">{{ $row['account_number'] }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-light-{{ $bank?->getAccountTypeBadgeColor() ?? 'secondary' }} fs-9">
                                        {{ $row['type_label'] }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-success fw-bold fs-7">
                                        Rp {{ number_format($row['total_incoming'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="text-danger fw-bold fs-7">
                                        Rp {{ number_format($row['total_outgoing'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @php $net = $row['net_cashflow']; @endphp
                                    <span class="fw-bold fs-7 {{ $net >= 0 ? 'text-primary' : 'text-warning' }}">
                                        {{ $net >= 0 ? '+' : '' }}Rp {{ number_format($net, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @php $balance = $row['current_balance']; @endphp
                                    @if($balance !== null)
                                        <span class="fw-bold fs-7 {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                            Rp {{ number_format($balance, 0, ',', '.') }}
                                        </span>
                                        @if($bank?->balance_updated_at)
                                            <div class="text-muted fs-9">{{ $bank->balance_updated_at->diffForHumans() }}</div>
                                        @endif
                                    @else
                                        <span class="text-muted fs-8">Belum ada transaksi</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($bank)
                                        <a href="{{ route('web.bank-accounts.cashflow', $bank) }}"
                                            class="btn btn-sm btn-light-primary py-1 px-3 fs-8">
                                            <i class="ki-outline ki-eye fs-7 me-1"></i>Detail
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Table --}}
    <div class="card">
        <div class="card-header pt-5">
            <h3 class="card-title fw-bold text-gray-800">Daftar Rekening Bank</h3>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                    <thead>
                        <tr class="fw-bold text-muted">
                            <th>Bank</th>
                            <th>Nomor Rekening</th>
                            <th>Atas Nama</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-center">Default Terima</th>
                            <th class="text-center">Default Kirim</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px">
                                            <div class="symbol-label bg-light-{{ $account->getAccountTypeBadgeColor() }} text-{{ $account->getAccountTypeBadgeColor() }} fw-bold fs-6">
                                                {{ strtoupper(substr($account->bank_name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-gray-900 fs-6 d-block">{{ $account->bank_name }}</span>
                                            @if($account->bank_code)
                                                <span class="badge badge-light-secondary fs-9">{{ $account->bank_code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-gray-800 fs-6 font-monospace">{{ $account->account_number }}</span>
                                </td>
                                <td>
                                    <span class="text-gray-700 fs-6">{{ $account->account_holder_name }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light-{{ $account->getAccountTypeBadgeColor() }} fw-bold">
                                        {{ $account->getAccountTypeLabel() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($account->default_for_receive)
                                        <span class="badge badge-success fw-bold">
                                            <i class="ki-outline ki-entrance-right fs-9 me-1"></i>
                                            #{{ $account->default_priority }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-9">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($account->default_for_send)
                                        <span class="badge badge-danger fw-bold">
                                            <i class="ki-outline ki-exit-right fs-9 me-1"></i>
                                            Kirim
                                        </span>
                                    @else
                                        <span class="text-muted fs-9">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($account->is_active)
                                        <span class="badge badge-light-success fw-bold">AKTIF</span>
                                    @else
                                        <span class="badge badge-light-secondary fw-bold">NONAKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <x-table-action>
                                        <x-table-action.item
                                            :href="route('web.bank-accounts.cashflow', $account)"
                                            icon="chart-line-up"
                                            label="Lihat Cashflow"
                                            color="info" />

                                        <x-table-action.item
                                            :href="route('web.bank-accounts.edit', $account)"
                                            icon="pencil"
                                            label="Edit"
                                            color="warning" />

                                        <x-table-action.divider />

                                        {{-- Set Default Receive --}}
                                        @if($account->is_active && $account->canReceive())
                                            <x-table-action.item
                                                icon="{{ $account->default_for_receive ? 'cross-circle' : 'entrance-right' }}"
                                                label="{{ $account->default_for_receive ? 'Hapus Default Terima' : 'Set Default Terima' }}"
                                                color="{{ $account->default_for_receive ? 'secondary' : 'success' }}"
                                                :form="['method' => 'PATCH', 'action' => route('web.bank-accounts.set-default-receive', $account)]"
                                                :confirm="$account->default_for_receive
                                                    ? 'Hapus rekening ini dari default penerimaan?'
                                                    : 'Jadikan rekening ini sebagai default penerimaan dari RS/Klinik?'" />
                                        @endif

                                        {{-- Set Default Send --}}
                                        @if($account->is_active && $account->canSend())
                                            <x-table-action.item
                                                icon="{{ $account->default_for_send ? 'cross-circle' : 'exit-right' }}"
                                                label="{{ $account->default_for_send ? 'Hapus Default Kirim' : 'Set Default Kirim' }}"
                                                color="{{ $account->default_for_send ? 'secondary' : 'danger' }}"
                                                :form="['method' => 'PATCH', 'action' => route('web.bank-accounts.set-default-send', $account)]"
                                                :confirm="$account->default_for_send
                                                    ? 'Hapus rekening ini dari default pengiriman?'
                                                    : 'Jadikan rekening ini sebagai default pengiriman ke Supplier?'" />
                                        @endif

                                        <x-table-action.divider />

                                        <x-table-action.item
                                            icon="{{ $account->is_active ? 'cross-square' : 'check-circle' }}"
                                            label="{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            color="{{ $account->is_active ? 'secondary' : 'success' }}"
                                            :form="['method' => 'PATCH', 'action' => route('web.bank-accounts.toggle-active', $account)]"
                                            :confirm="$account->is_active ? 'Nonaktifkan rekening ' . $account->bank_name . '?' : 'Aktifkan rekening ' . $account->bank_name . '?'" />

                                        <x-table-action.item
                                            icon="trash"
                                            label="Hapus"
                                            color="danger"
                                            :form="['method' => 'DELETE', 'action' => route('web.bank-accounts.destroy', $account)]"
                                            :confirm="'Hapus rekening ' . $account->bank_name . ' - ' . $account->account_number . '?'" />
                                    </x-table-action>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10">
                                    <x-empty-state icon="bank" title="Belum Ada Rekening"
                                        message="Tambahkan rekening bank Medikindo untuk mulai tracking cashflow." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($accounts->hasPages())
                <div class="d-flex justify-content-end mt-5">
                    {{ $accounts->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
