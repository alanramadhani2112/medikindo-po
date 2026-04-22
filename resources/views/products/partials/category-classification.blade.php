{{-- ================================================================
     SECTION: Klasifikasi Kategori Produk
     Digunakan di: products/create.blade.php & products/edit.blade.php
     ================================================================ --}}

<div class="col-12">
    <div class="separator separator-dashed my-7"></div>
    <h3 class="fs-5 fw-bold text-gray-900 mb-1">
        <i class="ki-outline ki-category fs-3 text-primary me-2"></i>
        Klasifikasi Kategori
    </h3>
    <p class="text-muted fs-7 mb-5">Klasifikasi produk berdasarkan regulasi, kelas, dan operasional.</p>
</div>

{{-- Row 1: Kategori Lama + Regulatory --}}
<div class="col-md-6">
    <div class="mb-5">
        <label class="form-label fs-6 fw-semibold">Kategori Produk (Lama)</label>
        <select name="category" class="form-select form-select-solid @error('category') is-invalid @enderror">
            <option value="">— Pilih Kategori —</option>
            @foreach($categories as $c)
                <option value="{{ $c }}" {{ old('category', $product->category ?? '') == $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
        <div class="form-text text-muted">Kategori operasional lama (tetap dipertahankan)</div>
        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="col-md-6">
    <div class="mb-5">
        <label class="form-label fs-6 fw-semibold">Regulatory Category</label>
        <select name="category_regulatory" id="category_regulatory"
                class="form-select form-select-solid @error('category_regulatory') is-invalid @enderror">
            <option value="">— Pilih Regulatory —</option>
            @foreach($categoryRegulatory as $val => $label)
                <option value="{{ $val }}" {{ old('category_regulatory', $product->category_regulatory ?? '') == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted">Klasifikasi berdasarkan regulasi BPOM</div>
        @error('category_regulatory') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Row 2: Class Category (dinamis) + Operational --}}
<div class="col-md-6">
    <div class="mb-5" id="category_class_wrapper">
        <label class="form-label fs-6 fw-semibold">
            Class Category
            <span id="category_class_required_badge" class="badge badge-danger ms-2 fs-8" style="display:none;">Wajib</span>
        </label>
        <select name="category_class" id="category_class"
                class="form-select form-select-solid @error('category_class') is-invalid @enderror">
            <option value="">— Pilih Class —</option>

            {{-- OBAT options --}}
            <optgroup label="Kelas Obat" id="optgroup_obat" style="display:none;">
                @foreach($categoryClassObat as $val => $label)
                    <option value="{{ $val }}" data-group="OBAT"
                        {{ old('category_class', $product->category_class ?? '') == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </optgroup>

            {{-- ALKES options --}}
            <optgroup label="Kelas Alat Kesehatan" id="optgroup_alkes" style="display:none;">
                @foreach($categoryClassAlkes as $val => $label)
                    <option value="{{ $val }}" data-group="ALKES"
                        {{ old('category_class', $product->category_class ?? '') == $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </optgroup>
        </select>
        <div class="form-text text-muted" id="category_class_hint">Pilih Regulatory Category terlebih dahulu</div>
        @error('category_class') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="col-md-6">
    <div class="mb-5">
        <label class="form-label fs-6 fw-semibold">Operational Category</label>
        <select name="category_operational"
                class="form-select form-select-solid @error('category_operational') is-invalid @enderror">
            <option value="">— Pilih Operational —</option>
            @foreach($categoryOperational as $val => $label)
                <option value="{{ $val }}" {{ old('category_operational', $product->category_operational ?? '') == $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div class="form-text text-muted">Klasifikasi untuk keperluan inventori dan pengadaan</div>
        @error('category_operational') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

@push('scripts')
<script>
(function() {
    const regulatorySelect = document.getElementById('category_regulatory');
    const classSelect      = document.getElementById('category_class');
    const optgroupObat     = document.getElementById('optgroup_obat');
    const optgroupAlkes    = document.getElementById('optgroup_alkes');
    const requiredBadge    = document.getElementById('category_class_required_badge');
    const classHint        = document.getElementById('category_class_hint');

    function updateClassOptions() {
        const val = regulatorySelect.value;

        // Hide all optgroups first
        optgroupObat.style.display  = 'none';
        optgroupAlkes.style.display = 'none';

        // Reset class select if regulatory changed
        const currentClass = classSelect.value;

        if (val === 'OBAT') {
            optgroupObat.style.display = '';
            classSelect.disabled = false;
            requiredBadge.style.display = '';
            classHint.textContent = 'Wajib diisi untuk produk Obat';
            // Keep selection if still valid
            if (!currentClass || !currentClass.match(/^(OBAT_KERAS|OBAT_BEBAS|OBAT_BEBAS_TERBATAS|NARKOTIKA|PSIKOTROPIKA|BIOLOGIS)$/)) {
                classSelect.value = '';
            }
        } else if (val === 'ALKES') {
            optgroupAlkes.style.display = '';
            classSelect.disabled = false;
            requiredBadge.style.display = '';
            classHint.textContent = 'Wajib diisi untuk Alat Kesehatan';
            if (!currentClass || !currentClass.match(/^KELAS_[ABCD]$/)) {
                classSelect.value = '';
            }
        } else {
            classSelect.disabled = true;
            classSelect.value = '';
            requiredBadge.style.display = 'none';
            classHint.textContent = val ? 'Class tidak diperlukan untuk kategori ini' : 'Pilih Regulatory Category terlebih dahulu';
        }
    }

    regulatorySelect.addEventListener('change', updateClassOptions);

    // Run on page load to restore state
    updateClassOptions();
})();
</script>
@endpush
