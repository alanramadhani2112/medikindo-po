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
    <div class="row g-5 mb-5">
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

    {{-- Credit Limit Rules Information --}}
    <div class="alert alert-warning d-flex align-items-start mb-7 border border-warning border-dashed">
        <i class="ki-outline ki-information-5 fs-2x text-warning me-4 mt-1"></i>
        <div class="flex-grow-1">
            <h4 class="fs-5 fw-bold text-gray-900 mb-3">Aturan Plafon Kredit Maksimum</h4>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-4 bg-light-warning rounded border border-warning border-dashed">
                        <div class="text-gray-800 fw-bold fs-6 mb-2">Rumah Sakit (RS/Hospital)</div>
                        <div class="text-warning fw-bold fs-2">Rp 20.000.000.000</div>
                        <div class="text-gray-600 fs-7 mt-1">Maksimum 20 Miliar Rupiah</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4 bg-light-warning rounded border border-warning border-dashed">
                        <div class="text-gray-800 fw-bold fs-6 mb-2">Klinik (Clinic)</div>
                        <div class="text-warning fw-bold fs-2">Rp 500.000.000</div>
                        <div class="text-gray-600 fs-7 mt-1">Maksimum 500 Juta Rupiah</div>
                    </div>
                </div>
            </div>
            <div class="mt-4 p-3 bg-light rounded">
                <div class="text-gray-700 fs-7 fw-semibold">
                    <i class="ki-outline ki-shield-tick fs-6 text-warning me-1"></i>
                    Plafon kredit yang ditetapkan tidak boleh melebihi batas maksimum sesuai tipe organisasi.
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
                    <form method="POST" action="{{ route('web.financial-controls.store') }}" id="creditLimitForm">
                        @csrf
                        <div class="mb-5">
                            <label class="form-label required fw-bold">Pilih Organisasi</label>
                            <select name="organization_id" id="organization_select" required class="form-select form-select-solid" onchange="updateMaxLimit()">
                                <option value="">— Pilih Organisasi —</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}" data-type="{{ strtolower($org->type) }}">
                                        {{ $org->name }} ({{ ucfirst($org->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('organization_id')
                                <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-5">
                            <label class="form-label required fw-bold">Plafon Kredit (Rp)</label>
                            <input type="number" name="max_limit" id="max_limit_input" required min="1"
                                class="form-control form-control-solid @error('max_limit') is-invalid @enderror"
                                placeholder="Contoh: 50000000"
                                value="{{ old('max_limit') }}">
                            <div class="form-text text-muted fs-7 mt-2" id="limit_hint">
                                Pilih organisasi untuk melihat plafon maksimum
                            </div>
                            @error('max_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-5 p-4 bg-light-info rounded border border-info border-dashed" id="limit_info" style="display: none;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="ki-outline ki-information-5 fs-2 text-info"></i>
                                <span class="fw-bold text-gray-800 fs-6">Informasi Plafon</span>
                            </div>
                            <div class="text-gray-700 fs-7">
                                <div class="mb-1">
                                    <strong>Tipe:</strong> <span id="org_type_display">-</span>
                                </div>
                                <div>
                                    <strong>Plafon Maksimum:</strong> <span id="max_limit_display" class="text-primary fw-bold">-</span>
                                </div>
                            </div>
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
            <form method="POST" action="{{ route('web.financial-controls.update', $limit) }}" id="editForm{{ $limit->id }}">
                @csrf @method('PATCH')
                <div class="modal-body">
                    @php
                        $orgType = strtolower($limit->organization?->type ?? 'clinic');
                        $maxAllowed = in_array($orgType, ['hospital', 'rs']) ? 20000000000 : 500000000;
                    @endphp
                    
                    <div class="mb-5 p-3 bg-light-info rounded border border-info border-dashed">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ki-outline ki-information-5 fs-3 text-info"></i>
                            <span class="fw-bold text-gray-800 fs-6">Batas Maksimum</span>
                        </div>
                        <div class="text-gray-700 fs-7">
                            <div class="mb-1">
                                <strong>Tipe Organisasi:</strong> {{ ucfirst($limit->organization?->type ?? '-') }}
                            </div>
                            <div>
                                <strong>Plafon Maksimum:</strong> 
                                <span class="text-primary fw-bold">Rp {{ number_format($maxAllowed, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <label class="form-label required fw-bold">Plafon Kredit Maksimum (Rp)</label>
                        <input type="number" 
                            name="max_limit" 
                            id="edit_max_limit_{{ $limit->id }}"
                            required 
                            min="1"
                            max="{{ $maxAllowed }}"
                            value="{{ $limit->max_limit }}"
                            data-max-allowed="{{ $maxAllowed }}"
                            data-org-type="{{ $limit->organization?->type ?? 'Clinic' }}"
                            class="form-control form-control-solid">
                        <div class="form-text text-muted fs-7 mt-2" id="edit_hint_{{ $limit->id }}">
                            Maksimum: Rp {{ number_format($maxAllowed, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="mb-5 p-4 bg-light-warning rounded border border-warning border-dashed">
                        <div class="text-gray-700 fs-7">
                            <div class="mb-2">
                                <strong>AR Berjalan:</strong> 
                                <span class="text-danger fw-bold">Rp {{ number_format($limit->total_active_ar, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <strong>Utilisasi Saat Ini:</strong> 
                                <span class="fw-bold">
                                    {{ number_format($limit->max_limit > 0 ? ($limit->total_active_ar / $limit->max_limit) * 100 : 0, 1) }}%
                                </span>
                            </div>
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

@push('scripts')
<script>
// Plafon maksimum berdasarkan tipe organisasi
const MAX_LIMITS = {
    'hospital': 20000000000,  // 20 Miliar
    'rs': 20000000000,         // 20 Miliar
    'clinic': 500000000,       // 500 Juta
    'klinik': 500000000,       // 500 Juta
    'default': 500000000       // Default: 500 Juta
};

function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function updateMaxLimit() {
    const select = document.getElementById('organization_select');
    const input = document.getElementById('max_limit_input');
    const hint = document.getElementById('limit_hint');
    const info = document.getElementById('limit_info');
    const typeDisplay = document.getElementById('org_type_display');
    const maxDisplay = document.getElementById('max_limit_display');
    
    if (!select.value) {
        input.value = '';
        input.max = '';
        hint.textContent = 'Pilih organisasi untuk melihat plafon maksimum';
        hint.classList.remove('text-danger');
        hint.classList.add('text-muted');
        info.style.display = 'none';
        return;
    }
    
    const selectedOption = select.options[select.selectedIndex];
    const orgType = selectedOption.dataset.type || 'default';
    const maxLimit = MAX_LIMITS[orgType] || MAX_LIMITS['default'];
    
    // Set max attribute untuk validasi HTML5
    input.max = maxLimit;
    
    // Auto-suggest plafon
    input.value = maxLimit;
    
    // Update hint
    hint.textContent = `Maksimum: ${formatRupiah(maxLimit)}`;
    hint.classList.remove('text-muted');
    hint.classList.add('text-primary');
    
    // Show info box
    info.style.display = 'block';
    typeDisplay.textContent = orgType.charAt(0).toUpperCase() + orgType.slice(1);
    maxDisplay.textContent = formatRupiah(maxLimit);
}

// Validasi saat submit form create
document.getElementById('creditLimitForm').addEventListener('submit', function(e) {
    const select = document.getElementById('organization_select');
    const input = document.getElementById('max_limit_input');
    
    if (!select.value) {
        e.preventDefault();
        alert('Pilih organisasi terlebih dahulu');
        return false;
    }
    
    const selectedOption = select.options[select.selectedIndex];
    const orgType = selectedOption.dataset.type || 'default';
    const maxLimit = MAX_LIMITS[orgType] || MAX_LIMITS['default'];
    const inputValue = parseInt(input.value);
    
    if (inputValue > maxLimit) {
        e.preventDefault();
        alert(`Plafon tidak boleh melebihi ${formatRupiah(maxLimit)} untuk ${orgType}`);
        return false;
    }
    
    if (inputValue < 1) {
        e.preventDefault();
        alert('Plafon harus lebih dari Rp 0');
        return false;
    }
});

// Validasi real-time saat input form create
document.getElementById('max_limit_input').addEventListener('input', function() {
    const select = document.getElementById('organization_select');
    const hint = document.getElementById('limit_hint');
    
    if (!select.value) return;
    
    const selectedOption = select.options[select.selectedIndex];
    const orgType = selectedOption.dataset.type || 'default';
    const maxLimit = MAX_LIMITS[orgType] || MAX_LIMITS['default'];
    const inputValue = parseInt(this.value);
    
    if (inputValue > maxLimit) {
        hint.textContent = `⚠️ Melebihi maksimum! Maksimum: ${formatRupiah(maxLimit)}`;
        hint.classList.remove('text-primary', 'text-muted');
        hint.classList.add('text-danger');
        this.classList.add('is-invalid');
    } else {
        hint.textContent = `Maksimum: ${formatRupiah(maxLimit)}`;
        hint.classList.remove('text-danger', 'text-muted');
        hint.classList.add('text-primary');
        this.classList.remove('is-invalid');
    }
});

// ═══════════════════════════════════════════════════════════
// VALIDASI UNTUK EDIT MODAL
// ═══════════════════════════════════════════════════════════

// Attach validation to all edit forms
document.querySelectorAll('form[id^="editForm"]').forEach(function(form) {
    const formId = form.id;
    const limitId = formId.replace('editForm', '');
    const input = document.getElementById('edit_max_limit_' + limitId);
    const hint = document.getElementById('edit_hint_' + limitId);
    
    if (!input || !hint) return;
    
    // Real-time validation for edit modal
    input.addEventListener('input', function() {
        const maxAllowed = parseInt(this.dataset.maxAllowed);
        const orgType = this.dataset.orgType;
        const inputValue = parseInt(this.value);
        
        if (inputValue > maxAllowed) {
            hint.textContent = `⚠️ Melebihi maksimum! Maksimum: ${formatRupiah(maxAllowed)}`;
            hint.classList.remove('text-primary', 'text-muted');
            hint.classList.add('text-danger');
            this.classList.add('is-invalid');
        } else {
            hint.textContent = `Maksimum: ${formatRupiah(maxAllowed)}`;
            hint.classList.remove('text-danger', 'text-muted');
            hint.classList.add('text-primary');
            this.classList.remove('is-invalid');
        }
    });
    
    // Form submission validation for edit modal
    form.addEventListener('submit', function(e) {
        const maxAllowed = parseInt(input.dataset.maxAllowed);
        const orgType = input.dataset.orgType;
        const inputValue = parseInt(input.value);
        
        if (inputValue > maxAllowed) {
            e.preventDefault();
            alert(`Plafon tidak boleh melebihi ${formatRupiah(maxAllowed)} untuk tipe organisasi ${orgType}`);
            return false;
        }
        
        if (inputValue < 1) {
            e.preventDefault();
            alert('Plafon harus lebih dari Rp 0');
            return false;
        }
    });
});
</script>
@endpush