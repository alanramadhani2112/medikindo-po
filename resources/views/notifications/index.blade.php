@extends('layouts.app')

@section('content')
    {{-- Header with Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-7 gap-3">
        <div>
            <h1 class="fs-2hx fw-bold text-gray-900 mb-2">
                <i class="ki-outline ki-notepad-bingn-on fs-2hx text-primary me-2"></i>
                Notifikasi
            </h1>
            <p class="text-gray-600 fs-6 mb-0">Kelola semua notifikasi dan update sistem Anda</p>
        </div>

        @if ($notifications->where('read_at', null)->count() > 0)
            <form method="POST" action="{{ route('web.notifications.mark_all_read') }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="ki-outline ki-double-check fs-3 me-2"></i>
                    Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    {{-- Stats Cards --}}
    <div class="row g-5 g-xl-8 mb-7">
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-primary">
                            <i class="ki-outline ki-notepad-bingn-bing fs-2x text-primary"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">Total Notifikasi</span>
                        <span class="text-gray-900 fw-bold fs-2">{{ $notifications->total() }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-success">
                            <i class="ki-outline ki-check-circle fs-2x text-success"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">Sudah Dibaca</span>
                        <span
                            class="text-gray-900 fw-bold fs-2">{{ $notifications->where('read_at', '!=', null)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-warning">
                            <i class="ki-outline ki-information-5 fs-2x text-warning"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">Belum Dibaca</span>
                        <span
                            class="text-gray-900 fw-bold fs-2">{{ $notifications->where('read_at', null)->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card card-flush h-100">
                <div class="card-body d-flex align-items-center py-5">
                    <div class="symbol symbol-50px me-4">
                        <span class="symbol-label bg-light-info">
                            <i class="ki-outline ki-calendar fs-2x text-info"></i>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-500 fw-semibold fs-7 d-block">Hari Ini</span>
                        <span
                            class="text-gray-900 fw-bold fs-2">{{ $notifications->where('created_at', '>=', now()->startOfDay())->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center mb-7">
            <i class="ki-outline ki-check-circle fs-2 text-success me-3"></i>
            <div class="flex-grow-1">
                <div class="fw-bold">Berhasil!</div>
                <div class="fs-7">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    {{-- Notifications List --}}
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex flex-column gap-4">
                @forelse($notifications as $notification)
                    @php
                        $isUnread = is_null($notification->read_at);
                        $data = $notification->data;
                        $icon = match ($data['type'] ?? 'default') {
                            'po_submitted' => 'document',
                            'po_approved' => 'check-circle',
                            'po_rejected' => 'cross-circle',
                            'goods_receipt' => 'package',
                            'invoice' => 'bill',
                            'payment' => 'wallet',
                            default => 'notification-bing',
                        };
                        $iconColor = match ($data['type'] ?? 'default') {
                            'po_approved', 'goods_receipt' => 'success',
                            'po_rejected' => 'danger',
                            'po_submitted' => 'warning',
                            'invoice', 'payment' => 'info',
                            default => 'primary',
                        };
                    @endphp

                    <div class="card {{ $isUnread ? 'border border-primary border-2' : '' }} hover-elevate-up">
                        <div class="card-body p-6">
                            <div class="d-flex align-items-start gap-5">
                                {{-- Icon --}}
                                <div class="symbol symbol-60px flex-shrink-0">
                                    <div class="symbol-label bg-light-{{ $iconColor }}">
                                        <i class="ki-outline ki-{{ $icon }} fs-2x text-{{ $iconColor }}"></i>
                                    </div>
                                </div>

                                {{-- Content --}}
                                <div class="flex-grow-1">
                                    <div
                                        class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-3">
                                        <div class="flex-grow-1">
                                            <h4 class="text-gray-900 fw-bold mb-2 fs-5">
                                                {{ $data['title'] ?? 'Notifikasi Sistem' }}
                                            </h4>
                                            <p class="text-gray-700 fs-6 mb-0">{{ $data['message'] ?? '' }}</p>
                                        </div>

                                        {{-- Unread Indicator --}}
                                        @if ($isUnread)
                                            <span class="badge badge-primary badge-lg flex-shrink-0">
                                                <i class="ki-outline ki-information-5 fs-6 me-1"></i>
                                                Baru
                                            </span>
                                        @endif
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center gap-3">
                                        <div class="d-flex align-items-center text-gray-500 fs-7">
                                            <i class="ki-outline ki-time fs-6 me-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>

                                        <div class="separator separator-dashed d-none d-md-block"
                                            style="width: 1px; height: 20px;"></div>

                                        <div class="d-flex align-items-center gap-2">
                                            @if (isset($data['action_url']))
                                                <a href="{{ $data['action_url'] }}" class="btn btn-sm btn-light-primary">
                                                    <i class="ki-outline ki-eye fs-5"></i>
                                                    Lihat Detail
                                                </a>
                                            @endif

                                            @if ($isUnread)
                                                <a href="{{ route('web.notifications.markAsRead', $notification->id) }}"
                                                    class="btn btn-sm btn-light">
                                                    <i class="ki-outline ki-check fs-5"></i>
                                                    Tandai Dibaca
                                                </a>
                                            @else
                                                <span class="badge badge-light-success">
                                                    <i class="ki-outline ki-double-check fs-6 me-1"></i>
                                                    Sudah Dibaca
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-20">
                            <div class="d-flex flex-column align-items-center">
                                <div class="symbol symbol-150px mb-7">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-notepad-bingn-status fs-5x text-primary"></i>
                                    </div>
                                </div>
                                <h3 class="text-gray-900 fs-2 fw-bold mb-3">Belum ada notifikasi</h3>
                                <p class="text-gray-600 fs-5 mb-0">Seluruh pesan dan update aktivitas sistem akan muncul di
                                    sini.</p>
                            </div>
                        </div>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if ($notifications->hasPages())
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-stack flex-wrap">
                                <div class="fs-6 fw-semibold text-gray-700">
                                    Menampilkan {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} dari
                                    {{ $notifications->total() }} notifikasi
                                </div>
                                <div>
                                    {{ $notifications->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
