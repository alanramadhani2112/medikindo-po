@extends('layouts.app', ['pageTitle' => 'Kendali Finansial'])

@section('content')
        {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-outline ki-check-circle fs-2 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="row g-5 mb-7">
        <div class="col-md-6">
            <div class="card bg-primary">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Total Fasilitas Kredit Aktif</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($limits->where('is_active', true)->sum('max_limit'), 0, ',', '.') }}</div>
                    <span class="badge badge-light-primary mt-2">{{ $limits->where('is_active', true)->count() }} organisasi aktif</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-danger">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Total AR Berjalan (Piutang)</span>
                    <div class="text-white fs-2x fw-bold mt-2">Rp {{ number_format($limits->sum('total_active_ar'), 0, ',', '.') }}</div>
                    <span class="badge badge-light-danger mt-2">Estimasi piutang aktif keseluruhan</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5">
        {{-- CREDIT LIMIT TABLE --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-shield-tick fs-2 me-2"></i>
                        Limit Kredit Per Organisasi
                    </h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-primary">{{ $limits->count() }} organisasi dikonfigurasi</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 rounded-start min-w-200px">Organisasi</th>
                                    <th class="text-end min-w-120px">Plafon Kredit</th>
                                    <th class="text-end min-w-120px">AR Berjalan</th>
                                    <th class="min-w-150px">Utilisasi</th>
                                    <th class="text-center min-w-100px">Status</th>
                                    <th class="text-center pe-4 rounded-end min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($limits as $limit)
                                    @php
                                        $usagePercent = $limit->max_limit > 0
                                            ? ($limit->total_active_ar / $limit->max_limit) * 100
                                            : ($limit->total_active_ar > 0 ? 100 : 0);
                                        $barColor = $usagePercent > 90 ? 'bg-danger' : ($usagePercent > 70 ? 'bg-warning' : 'bg-primary');
                                        $textColor = $usagePercent > 90 ? 'text-danger' : ($usagePercent > 70 ? 'text-warning' : 'text-primary');
                                    @endphp
                                    <tr class="{{ $usagePercent > 90 ? 'bg-light-danger' : '' }}">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="symbol symbol-40px">
                                                    <div class="symbol-label bg-light-primary text-primary fw-bold fs-6">
                                                        {{ strtoupper(substr($limit->organization?->name ?? 'O', 0, 2)) }}
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold text-gray-900 fs-6">{{ $limit->organization?->name ?? '—' }}</span>
                                                    <span class="text-muted fs-7">{{ $limit->organization?->type ?? '' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-primary fs-6">Rp {{ number_format($limit->max_limit, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold {{ $usagePercent > 90 ? 'text-danger' : ($usagePercent > 70 ? 'text-warning' : 'text-primary') }} fs-6">
                                                Rp {{ number_format($limit->total_active_ar, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="flex-grow-1">
                                                    <div class="progress h-8px">
                                                        <div class="progress-bar {{ $barColor }}" role="progressbar" 
                                                             style="width: {{ min(100, $usagePercent) }}%"></div>
                                                    </div>
                                                </div>
                                                <span class="fw-bold {{ $textColor }} fs-7" style="min-width: 40px;">{{ number_format($usagePercent, 0) }}%</span>
                                            </div>
                                            @if($usagePercent > 90)
                                                <span class="badge badge-light-danger fs-8 mt-1">⚠ Mendekati Batas!</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($limit->is_active)
                                                <span class="badge badge-light-success">
                                                    <span class="bullet bullet-dot bg-success me-1"></span>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="badge badge-light-secondary">
                                                    <span class="bullet bullet-dot bg-secondary me-1"></span>
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light btn-active-light-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="ki-outline ki-dots-horizontal fs-3"></i>
                                                    Aksi
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editModal{{ $limit->id }}">
                                                            <i class="ki-outline ki-pencil fs-3 me-2 text-primary"></i>
                                                            Edit Plafon
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('web.financial-controls.update', $limit) }}" class="d-inline">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="max_limit" value="{{ $limit->max_limit }}">
                                                            @if($limit->is_active)
                                                                <button type="submit" name="is_active" value="0" class="dropdown-item text-warning">
                                                                    <i class="ki-outline ki-cross-circle fs-3 me-2"></i>
                                                                    Nonaktifkan
                                                                </button>
                                                            @else
                                                                <input type="hidden" name="is_active" value="1">
                                                                <button type="submit" class="dropdown-item text-success">
                                                                    <i class="ki-outline ki-check-circle fs-3 me-2"></i>
                                                                    Aktifkan
                                                                </button>
                                                            @endif
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-10">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="ki-outline ki-shield-tick fs-3x text-gray-400 mb-3"></i>
                                                <h3 class="fs-5 fw-bold text-gray-800 mb-1">Belum Ada Kebijakan Kredit</h3>
                                                <p class="text-muted fs-7">Terapkan limit kredit untuk setiap organisasi di sini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- SIDEBAR FORM --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-outline ki-plus fs-2 me-2"></i>
                        Terapkan Limit Kredit Baru
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('web.financial-controls.store') }}">
                        @csrf
                        
                        <div class="mb-5">
                            <label class="form-label required fw-semibold fs-6 mb-2">Pilih Organisasi</label>
                            <select name="organization_id" required class="form-select form-select-solid">
                                <option value="">— Pilih Organisasi —</option>
                                @foreach($organizations as $organization)
                                    <option value="{{ $organization->id }}">{{ $organization->name }} ({{ ucfirst($organization->type) }})</option>
                                @endforeach
                            </select>
                            @error('organization_id') 
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5">
                            <label class="form-label required fw-semibold fs-6 mb-2">Plafon Kredit Maksimum (Rp)</label>
                            <input type="number" name="max_limit" required min="1" 
                                   placeholder="Contoh: 50000000"
                                   class="form-control form-control-solid">
                            @error('max_limit') 
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-5 p-4 bg-light-warning rounded border border-warning">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" checked 
                                       id="is_active_check" class="form-check-input">
                                <label for="is_active_check" class="form-check-label fw-semibold text-gray-800">
                                    Aktifkan pemblokiran otomatis jika organisasi melebihi plafon kredit.
                                </label>
                            </div>
                        </div>

                        <div class="separator my-5"></div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ki-outline ki-check fs-3"></i>
                            Simpan Kebijakan Kredit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- EDIT MODALS --}}
    @foreach($limits as $limit)
    <div class="modal fade" id="editModal{{ $limit->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="ki-outline ki-pencil fs-2 me-2 text-primary"></i>
                        Edit Plafon Kredit
                    </h3>
                    <button type="button" class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-outline ki-cross fs-1"></i>
                    </button>
                </div>
                <form method="POST" action="{{ route('web.financial-controls.update', $limit) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="mb-5">
                            <label class="form-label fw-semibold fs-6 mb-2">Organisasi</label>
                            <input type="text" class="form-control form-control-solid" value="{{ $limit->organization?->name }}" disabled>
                        </div>

                        <div class="mb-5">
                            <label class="form-label required fw-semibold fs-6 mb-2">Plafon Kredit Maksimum (Rp)</label>
                            <input type="number" name="max_limit" required min="1" 
                                   value="{{ $limit->max_limit }}"
                                   class="form-control form-control-solid"
                                   placeholder="Contoh: 50000000">
                            <div class="form-text">
                                Plafon saat ini: <strong>Rp {{ number_format($limit->max_limit, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="mb-5 p-4 bg-light-info rounded border border-info">
                            <div class="d-flex align-items-start gap-3">
                                <i class="ki-outline ki-information-5 fs-2x text-info"></i>
                                <div>
                                    <div class="fw-bold text-gray-900 mb-1">Informasi Utilisasi</div>
                                    <div class="text-gray-700 fs-7">
                                        AR Berjalan: <strong>Rp {{ number_format($limit->total_active_ar, 0, ',', '.') }}</strong><br>
                                        Utilisasi: <strong>{{ number_format(($limit->max_limit > 0 ? ($limit->total_active_ar / $limit->max_limit) * 100 : 0), 1) }}%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" {{ $limit->is_active ? 'checked' : '' }}
                                   id="is_active_edit_{{ $limit->id }}" class="form-check-input">
                            <label for="is_active_edit_{{ $limit->id }}" class="form-check-label fw-semibold text-gray-800">
                                Aktifkan pemblokiran otomatis
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ki-outline ki-cross fs-3"></i>
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-outline ki-check fs-3"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endsection
