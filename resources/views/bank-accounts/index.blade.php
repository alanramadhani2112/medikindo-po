@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">Akun Bank</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola rekening bank Medikindo sebagai tujuan pembayaran dari RS/Klinik</p>
        </div>
        @can('manage_bank_accounts')
            <a href="{{ route('web.bank-accounts.create') }}" class="btn btn-primary">
                <i class="ki-outline ki-plus fs-4 me-1"></i>Tambah Rekening
            </a>
        @endcan
    </div>

    {{-- Summary Cards --}}
    <div class="row g-5 mb-7">
        <div class="col-md-4">
            <div class="card" style="background: linear-gradient(135deg, #1b4b7f 0%, #153a63 100%);">
                <div class="card-body">
                    <span class="text-white opacity-75 fs-7 fw-bold">Total Rekening</span>
                    <div class="text-white fs-2x fw-bold mt-1">{{ $accounts->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success">
                <div class="card-body">
                    <span class="text-white opacity-75 fs-7 fw-bold">Rekening Aktif</span>
                    <div class="text-white fs-2x fw-bold mt-1">{{ \App\Models\BankAccount::where('is_active', true)->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning">
                <div class="card-body">
                    <span class="text-white opacity-75 fs-7 fw-bold">Rekening Default</span>
                    @php $default = \App\Models\BankAccount::where('is_default', true)->first(); @endphp
                    <div class="text-white fs-6 fw-bold mt-1">
                        {{ $default ? $default->bank_name . ' - ' . $default->account_number : 'Belum ada' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
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
                            <th class="text-center">Status</th>
                            <th class="text-center">Default</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px">
                                            <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                                {{ strtoupper(substr($account->bank_name, 0, 2)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-gray-900 fs-6 d-block">{{ $account->bank_name }}</span>
                                            @if($account->bank_code)
                                                <span class="badge badge-light-secondary fs-9">Kode: {{ $account->bank_code }}</span>
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
                                    @if($account->is_active)
                                        <span class="badge badge-light-success fw-bold">AKTIF</span>
                                    @else
                                        <span class="badge badge-light-secondary fw-bold">NONAKTIF</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($account->is_default)
                                        <span class="badge badge-warning fw-bold">
                                            <i class="ki-outline ki-star fs-8 me-1"></i>DEFAULT
                                        </span>
                                    @else
                                        <span class="text-muted fs-8">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <x-table-action>
                                        <x-table-action.item :href="route('web.bank-accounts.edit', $account)" icon="pencil" label="Edit" color="warning" />

                                        @if($account->is_active && !$account->is_default)
                                            <x-table-action.item
                                                icon="star"
                                                label="Jadikan Default"
                                                color="primary"
                                                :form="['method' => 'PATCH', 'action' => route('web.bank-accounts.set-default', $account)]"
                                                confirm="Jadikan rekening ini sebagai default untuk invoice baru?" />
                                        @endif

                                        <x-table-action.item
                                            icon="{{ $account->is_active ? 'cross-square' : 'check-circle' }}"
                                            label="{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            color="{{ $account->is_active ? 'danger' : 'success' }}"
                                            :form="['method' => 'PATCH', 'action' => route('web.bank-accounts.toggle-active', $account)]"
                                            :confirm="$account->is_active ? 'Nonaktifkan rekening ' . $account->bank_name . '?' : 'Aktifkan rekening ' . $account->bank_name . '?'" />

                                        <x-table-action.divider />

                                        <x-table-action.item
                                            icon="trash"
                                            label="Hapus"
                                            color="danger"
                                            :form="['method' => 'DELETE', 'action' => route('web.bank-accounts.destroy', $account)]"
                                            :confirm="'Hapus rekening ' . $account->bank_name . ' - ' . $account->account_number . '? Tidak dapat dibatalkan.'" />
                                    </x-table-action>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <x-empty-state icon="bank" title="Belum Ada Rekening"
                                        message="Tambahkan rekening bank Medikindo sebagai tujuan pembayaran dari RS/Klinik." />
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
