<x-layout title="Manajemen Persetujuan" pageTitle="Manajemen Persetujuan" :breadcrumbs="$breadcrumbs">

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-solid ki-check-circle fs-2 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('web.approvals.index') }}" method="GET" class="d-flex flex-wrap gap-3">
                <input type="hidden" name="tab" value="{{ $tab ?? 'pending' }}">
                <div class="flex-grow-1" style="max-width: 400px;">
                    <div class="position-relative">
                        <i class="ki-solid ki-magnifier fs-3 position-absolute top-50 translate-middle-y ms-4"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control form-control-solid ps-12" 
                               placeholder="Cari nomor PO atau supplier...">
                    </div>
                </div>
                <button type="submit" class="btn btn-light-primary">
                    <i class="ki-solid ki-magnifier fs-2"></i>
                    Cari
                </button>
            </form>
        </div>
    </div>

    {{-- TABS --}}
    <div class="card mb-5">
        <div class="card-header border-0 pt-6 pb-2">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
                @php
                    $tabOptions = [
                        'pending' => ['label' => 'Antrian Persetujuan', 'icon' => 'ki-time'],
                        'history' => ['label' => 'Riwayat Keputusan', 'icon' => 'ki-document'],
                    ];
                @endphp
                @foreach($tabOptions as $val => $tabData)
                    @php 
                        $isActive = ($tab ?? 'pending') === $val;
                        $count = $counts[$val] ?? 0;
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('web.approvals.index', array_merge(request()->except(['tab', 'page']), ['tab' => $val])) }}" 
                           class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                            <i class="ki-solid {{ $tabData['icon'] }} fs-4 me-2"></i>
                            <span class="fs-6 fw-bold">{{ $tabData['label'] }}</span>
                            <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-secondary' }} ms-auto">
                                {{ $count }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- CONTENT LIST --}}
    @if(($tab ?? 'pending') === 'pending')
        {{-- PENDING APPROVALS --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-solid ki-time fs-2 me-2"></i>
                    Antrian Persetujuan
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 rounded-start">Nomor PO</th>
                                <th>Informasi Transaksi</th>
                                <th>Status</th>
                                <th>Level Persetujuan</th>
                                <th class="text-end">Nilai PO</th>
                                <th class="text-center pe-4 rounded-end min-w-200px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingApprovals as $po)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('web.po.show', $po) }}" class="text-gray-900 text-hover-primary fw-bold fs-6">
                                            {{ $po->po_number }}
                                        </a>
                                        <div class="text-muted fs-7 mt-1">
                                            <i class="ki-solid ki-time fs-7 me-1"></i>
                                            {{ $po->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-gray-800 fs-6 mb-1">{{ $po->organization?->name }}</div>
                                        <div class="text-muted fs-7">
                                            <i class="ki-solid ki-arrow-right-left fs-7 me-1"></i>
                                            {{ $po->supplier?->name }}
                                        </div>
                                        <div class="text-muted fs-8 mt-1">
                                            <i class="ki-solid ki-user fs-8 me-1"></i>
                                            {{ $po->creator?->name }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($po->status) {
                                                'draft' => 'secondary',
                                                'submitted' => 'warning',
                                                'approved' => 'success',
                                                'shipped' => 'primary',
                                                'delivered', 'completed' => 'success',
                                                'rejected', 'cancelled' => 'danger',
                                                default => 'primary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusColor }}">{{ strtoupper($po->status) }}</span>
                                        @if($po->has_narcotics)
                                            <span class="badge badge-danger d-block mt-1">⚠ NARKOTIKA</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php $pendingApproval = $po->approvals->filter(fn($a) => $a->status === 'pending')->first(); @endphp
                                        @if($pendingApproval)
                                            <div class="badge badge-light-warning fs-7 fw-semibold">
                                                <span class="bullet bullet-dot bg-warning me-2"></span>
                                                Level {{ $pendingApproval->level }}
                                            </div>
                                            <div class="text-muted fs-8 mt-1">
                                                {{ $pendingApproval->level === 2 ? 'Verifikasi Narkotika' : 'Review Anggaran' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex flex-column gap-2">
                                            <input type="text" 
                                                   id="notes_{{ $po->id }}"
                                                   placeholder="Catatan (opsional)..." 
                                                   class="form-control form-control-sm form-control-solid">
                                            <div class="d-flex gap-2">
                                                <form method="POST" action="{{ route('web.approvals.process', $po) }}" class="flex-fill">
                                                    @csrf
                                                    @if($pendingApproval) <input type="hidden" name="level" value="{{ $pendingApproval->level }}"> @endif
                                                    <input type="hidden" name="decision" value="approved">
                                                    <input type="hidden" name="notes" id="notes_approved_{{ $po->id }}">
                                                    <button type="submit" class="btn btn-sm btn-success w-100 submit-confirm" 
                                                            data-title="Konfirmasi Persetujuan"
                                                            data-message="Apakah Anda yakin ingin <strong>menyetujui</strong> pengajuan PO ini?"
                                                            data-confirm-text="<i class='ki-solid ki-check fs-3 me-2'></i>Ya, Setujui!"
                                                            onclick="document.getElementById('notes_approved_{{ $po->id }}').value = document.getElementById('notes_{{ $po->id }}').value;">
                                                        <i class="ki-solid ki-check fs-4"></i>
                                                        Setujui
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('web.approvals.process', $po) }}" class="flex-fill">
                                                    @csrf
                                                    @if($pendingApproval) <input type="hidden" name="level" value="{{ $pendingApproval->level }}"> @endif
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <input type="hidden" name="notes" id="notes_rejected_{{ $po->id }}">
                                                    <button type="submit" class="btn btn-sm btn-danger w-100 submit-confirm"
                                                            data-title="Konfirmasi Penolakan"
                                                            data-message="Apakah Anda yakin ingin <strong>menolak</strong> pengajuan PO ini?"
                                                            data-confirm-text="<i class='ki-solid ki-cross fs-3 me-2'></i>Ya, Tolak!"
                                                            onclick="document.getElementById('notes_rejected_{{ $po->id }}').value = document.getElementById('notes_{{ $po->id }}').value;">
                                                        <i class="ki-solid ki-cross fs-4"></i>
                                                        Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-10">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ki-solid ki-check-circle fs-3x text-success mb-3"></i>
                                            <h3 class="fs-5 fw-bold text-gray-800 mb-1">Antrian Kosong</h3>
                                            <p class="text-muted fs-7">Tidak ada pengajuan yang memerlukan persetujuan Anda saat ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- HISTORY VIEW --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ki-solid ki-document fs-2 me-2"></i>
                    Riwayat Keputusan
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-bordered table-row-gray-300 align-middle gs-7 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 rounded-start">Nomor PO</th>
                                <th>Informasi Transaksi</th>
                                <th>Status Akhir</th>
                                <th>Jejak Persetujuan</th>
                                <th class="text-end pe-4 rounded-end">Nilai PO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingApprovals as $po)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('web.po.show', $po) }}" class="text-gray-900 text-hover-primary fw-bold fs-6">
                                            {{ $po->po_number }}
                                        </a>
                                        <div class="text-muted fs-7 mt-1">
                                            <i class="ki-solid ki-time fs-7 me-1"></i>
                                            {{ $po->updated_at->format('d/m/Y H:i') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-gray-800 fs-6 mb-1">{{ $po->organization?->name }}</div>
                                        <div class="text-muted fs-7">
                                            <i class="ki-solid ki-arrow-right-left fs-7 me-1"></i>
                                            {{ $po->supplier?->name }}
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $statusColor = match($po->status) {
                                                'draft' => 'secondary',
                                                'submitted' => 'warning',
                                                'approved' => 'success',
                                                'shipped' => 'primary',
                                                'delivered', 'completed' => 'success',
                                                'rejected', 'cancelled' => 'danger',
                                                default => 'primary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $statusColor }}">{{ strtoupper($po->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            @foreach($po->approvals as $appr)
                                                @php
                                                    $appColor = match($appr->status) {
                                                        'approved' => 'badge-success',
                                                        'rejected' => 'badge-danger',
                                                        default => 'badge-warning'
                                                    };
                                                @endphp
                                                <div class="badge {{ $appColor }} me-2" 
                                                     data-bs-toggle="tooltip" 
                                                     title="Level {{ $appr->level }}: {{ $appr->approver?->name ?? 'System' }} ({{ strtoupper($appr->status) }})">
                                                    {{ $appr->level }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="fw-bold text-gray-900 fs-6">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-10">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ki-solid ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                            <span class="text-gray-500 fs-6">Belum ada riwayat keputusan.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- PAGINATION --}}
    @if($pendingApprovals->hasPages())
        <div class="d-flex flex-stack flex-wrap pt-7">
            {{ $pendingApprovals->links() }}
        </div>
    @endif

</x-layout>