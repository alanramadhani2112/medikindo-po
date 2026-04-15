{{-- PERIOD SELECTOR COMPONENT --}}
{{-- Purpose: Reusable date range selector for dashboard analytics --}}
{{-- Usage: <x-period-selector :current-period="$currentPeriod" /> --}}

@props(['currentPeriod' => 'today'])

<div class="card card-flush mb-5">
    <div class="card-body p-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            {{-- Label --}}
            <div class="d-flex align-items-center">
                <i class="ki-outline ki-calendar fs-2 text-primary me-3"></i>
                <div>
                    <h4 class="mb-0 fs-6 fw-bold text-gray-900">Periode Data</h4>
                    <span class="text-gray-600 fs-7">Pilih rentang waktu untuk analisis</span>
                </div>
            </div>

            {{-- Period Buttons --}}
            <div class="d-flex flex-wrap gap-2">
                <button type="button" 
                        class="btn btn-sm {{ $currentPeriod === 'today' ? 'btn-primary' : 'btn-light-primary' }} period-btn" 
                        data-period="today">
                    <i class="ki-outline ki-calendar-tick fs-5 me-1"></i>
                    Hari Ini
                </button>
                <button type="button" 
                        class="btn btn-sm {{ $currentPeriod === 'week' ? 'btn-primary' : 'btn-light-primary' }} period-btn" 
                        data-period="week">
                    <i class="ki-outline ki-calendar fs-5 me-1"></i>
                    Minggu Ini
                </button>
                <button type="button" 
                        class="btn btn-sm {{ $currentPeriod === 'month' ? 'btn-primary' : 'btn-light-primary' }} period-btn" 
                        data-period="month">
                    <i class="ki-outline ki-calendar-2 fs-5 me-1"></i>
                    Bulan Ini
                </button>
                <button type="button" 
                        class="btn btn-sm {{ $currentPeriod === 'year' ? 'btn-primary' : 'btn-light-primary' }} period-btn" 
                        data-period="year">
                    <i class="ki-outline ki-calendar-8 fs-5 me-1"></i>
                    Tahun Ini
                </button>
                <button type="button" 
                        class="btn btn-sm {{ $currentPeriod === 'custom' ? 'btn-primary' : 'btn-light-primary' }}" 
                        data-bs-toggle="modal" 
                        data-bs-target="#customPeriodModal">
                    <i class="ki-outline ki-setting-2 fs-5 me-1"></i>
                    Custom
                </button>
            </div>
        </div>

        {{-- Current Period Display --}}
        <div class="mt-4 pt-4 border-top border-gray-300">
            <div class="d-flex align-items-center">
                <span class="text-gray-600 fs-7 me-2">Menampilkan data:</span>
                <span class="badge badge-light-primary fs-7 fw-bold" id="current-period-display">
                    @switch($currentPeriod)
                        @case('today')
                            {{ now()->format('d M Y') }}
                            @break
                        @case('week')
                            {{ now()->startOfWeek()->format('d M') }} - {{ now()->endOfWeek()->format('d M Y') }}
                            @break
                        @case('month')
                            {{ now()->format('F Y') }}
                            @break
                        @case('year')
                            {{ now()->format('Y') }}
                            @break
                        @default
                            {{ now()->format('d M Y') }}
                    @endswitch
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Custom Period Modal --}}
<div class="modal fade" id="customPeriodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Pilih Periode Custom</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="customPeriodForm">
                    <div class="mb-5">
                        <label class="form-label required">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" required>
                    </div>
                    <div class="mb-5">
                        <label class="form-label required">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="applyCustomPeriod">
                    <i class="ki-outline ki-check fs-5 me-1"></i>
                    Terapkan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Period button click handler
    document.querySelectorAll('.period-btn').forEach(button => {
        button.addEventListener('click', function() {
            const period = this.dataset.period;
            
            // Update URL with period parameter
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);
            window.location.href = url.toString();
        });
    });

    // Custom period apply handler
    document.getElementById('applyCustomPeriod')?.addEventListener('click', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;

        if (!startDate || !endDate) {
            alert('Mohon isi tanggal mulai dan tanggal akhir');
            return;
        }

        if (new Date(startDate) > new Date(endDate)) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
            return;
        }

        // Update URL with custom period
        const url = new URL(window.location.href);
        url.searchParams.set('period', 'custom');
        url.searchParams.set('start_date', startDate);
        url.searchParams.set('end_date', endDate);
        window.location.href = url.toString();
    });
});
</script>
