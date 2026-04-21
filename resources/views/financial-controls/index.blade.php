@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-1">Kendali Finansial</h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola limit kredit (plafon) organisasi dan kontrol AR</p>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-5 mb-7">
        <div class="col-md-6">
            <div class="card" style="background: linear-gradient(135deg, #1b4b7f 0%, #153a63 100%);">
                <div class="card-body">
                    <span class="text-white opacity-75 fs-7 fw-bold">Total Fasilitas Kredit Aktif</span>
                    <div class="text-white fs-2x fw-bold mt-2">
                        Rp {{ number_format($limits->where('is_active', true)->sum('max_limit'), 0, ',', '.') }}
                    </div>
                    <span class="badge badge-light-primary mt-2">
                        {{ $limits->where('is_active', true)->count() }} organisasi aktif
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-danger">
                <div class="card-body">
                    <span class="text-white opacity-75 fs-7 fw-bold">Total AR Berjalan (Piutang)</span>
                    <div class="text-white fs-2x fw-bold mt-2">
                        Rp {{ number_format($limits->sum('total_active_ar'), 0, ',', '.') }}
                    </div>
                    <span class="badge badge-light-danger mt-2">Estimasi piutang aktif keseluruhan</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">

        {{-- Credit Limit Table --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Limit Kredit Per Organisasi</h3>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mb-0">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th>Organisasi</th>
                                    <th class="text-end">Plafon</th>
                                    <th class="text-end">AR Aktif</th>
                                    <th>Utilisasi</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($limits as $limit)
                                    @php
                                        $usagePercent = $limit->max_limit > 0
                                            ? ($limit->total_active_ar / $limit->max_limit) * 100
                                            : ($limit->total_active_ar > 0 ? 100 : 0);
                                        $barColor  = $usagePercent > 90 ? 'bg-danger'  : ($usagePercent > 70 ? 'bg-warning'  : 'bg-primary');
                                        $textColor = $usagePercent > 90 ? 'text-danger' : ($usagePercent > 70 ? 'text-warning' : 'text-primary');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="symbol symbol-40px">
                                                    <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                                        {{ strtoupper(substr($limit->organization?->name ?? 'O', 0, 2)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-gray-900 fs-6 d-block">{{ $limit->organization?->name ?? '—' }}</span>
                                                    <span class="text-muted fs-7">{{ $limit->organization?->type ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-gray-800">Rp {{ number_format($limit->max_limit, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ $usagePercent > 90 ? 'text-danger' : 'text-gray-800' }}">
                                                Rp {{ number_format($limit->total_active_ar, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td style="min-width: 140px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="flex-grow-1">
                                                    <div class="progress h-6px">
                                                        <div class="progress-bar {{ $barColor }}" style="width: {{ min(100, $usagePercent) }}%"></div>
                                                    </div>
                                                </div>
                                                <span class="fw-bold {{ $textColor }} fs-7">{{ number_format($usagePercent, 0) }}%</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($limit->is_active)
                                                <span class="badge badge-light-success">AKTIF</span>
                                            @else
                                                <span class="badge badge-light-secondary">NONAKTIF</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <x-table-action>
                                                <x-table-action.item icon="pencil" label="Edit Plafon" color="warning" :modalTarget="'#editModal' . $limit->id" />
                                                <x-table-action.divider />
                                                <x-table-action.item
                                                    icon="{{ $limit->is_active ? 'cross-square' : 'check-circle' }}"
                                                    label="{{ $limit->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    color="{{ $limit->is_active ? 'danger' : 'success' }}"
                                                    :form="['method' => 'PATCH', 'action' => route('web.financial-controls.update', $limit), 'fields' => ['max_limit' => $limit->max_limit, 'is_active' => $limit->is_active ? '0' : '1']]"
                                                    :confirm="$limit->is_active ? 'Nonaktifkan limit kredit untuk ' . ($limit->organization?->name ?? 'organisasi ini') . '?' : 'Aktifkan limit kredit untuk ' . ($limit->organization?->name ?? 'organisasi ini') . '?'" />
                                            </x-table-action>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10">
                                            <x-empty-state icon="shield-tick" title="Tidak Ada Data"
                                                message="Belum ada kebijakan limit kredit yang diterapkan." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar: Add New Limit --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header pt-5">
                    <h3 class="card-title fw-bold text-gray-800">Terapkan Limit Baru</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.financial-controls.store') }}">
                        @csrf
                        <div class="mb-5">
                            <label class="form-label required fw-bold">Pilih Organisasi</label>
                            <select name="organization_id" required class="form-select form-select-solid">
                                <option value="">— Pilih Organisasi —</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }} ({{ ucfirst($org->type) }})</option>
                                @endforeach
                            </select>
                            @error('organization_id')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-5">
                            <label class="form-label required fw-bold">Plafon Kredit (Rp)</label>
                            <input type="number" name="max_limit" required min="1"
                                class="form-control form-control-solid @error('max_limit') is-invalid @enderror"
                                placeholder="Contoh: 50000000"
                                value="{{ old('max_limit') }}">
                            @error('max_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-8 p-4 bg-light-warning rounded border border-warning border-dashed">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" checked
                                    id="is_active_check" class="form-check-input">
                                <label for="is_active_check" class="form-check-label fw-semibold text-gray-800 fs-7">
                                    Aktifkan pemblokiran otomatis jika melebihi plafon.
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ki-outline ki-check fs-3 me-1"></i>Simpan Kebijakan
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>{{-- end row --}}

</div>{{-- end container --}}

{{-- ═══════════════════════════════════════════════════════════
     EDIT MODALS — rendered at body level, outside all cards
     ═══════════════════════════════════════════════════════════ --}}
@foreach($limits as $limit)
<div class="modal fade" id="editModal{{ $limit->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title fs-5 fw-bold">
                    <i class="ki-outline ki-pencil fs-4 me-2 text-warning"></i>
                    Edit Plafon: {{ $limit->organization?->name }}
                </h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('web.financial-controls.update', $limit) }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="form-label required fw-bold">Plafon Kredit Maksimum (Rp)</label>
                        <input type="number" name="max_limit" required min="1"
                            value="{{ $limit->max_limit }}"
                            class="form-control form-control-solid">
                    </div>
                    <div class="mb-5 p-4 bg-light-info rounded border border-info border-dashed">
                        <div class="text-gray-700 fs-7">
                            AR Berjalan: <strong>Rp {{ number_format($limit->total_active_ar, 0, ',', '.') }}</strong><br>
                            Utilisasi: <strong>
                                {{ number_format($limit->max_limit > 0 ? ($limit->total_active_ar / $limit->max_limit) * 100 : 0, 1) }}%
                            </strong>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1"
                            {{ $limit->is_active ? 'checked' : '' }}
                            id="is_active_edit_{{ $limit->id }}"
                            class="form-check-input">
                        <label for="is_active_edit_{{ $limit->id }}"
                            class="form-check-label fw-semibold text-gray-800">
                            Aktifkan pemblokiran otomatis
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ki-outline ki-check fs-4 me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection
