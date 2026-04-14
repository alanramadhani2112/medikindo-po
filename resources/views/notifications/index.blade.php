@extends('layouts.app')

@section('content')
        {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-5">
            <i class="ki-duotone ki-check-circle fs-2 me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- Notifications List --}}
    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-12">
            <div class="d-flex flex-column gap-5">
                @forelse($notifications as $notification)
                    @php
                        $isUnread = is_null($notification->read_at);
                        $data = $notification->data;
                        $icon = match($data['type'] ?? '') {
                            'po_submitted' => 'ki-document',
                            'po_approved' => 'ki-check-circle',
                            'po_rejected' => 'ki-cross-circle',
                            default => 'ki-notification-bing',
                        };
                        $badgeColor = match($data['type'] ?? '') {
                            'po_approved', 'po_submitted' => 'success',
                            'po_rejected' => 'danger',
                            default => 'primary',
                        };
                    @endphp
                    
                    <div class="card {{ $isUnread ? 'border-primary' : '' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-4">
                                {{-- Icon --}}
                                <div class="symbol symbol-50px">
                                    <div class="symbol-label bg-light-{{ $badgeColor }} text-{{ $badgeColor }}">
                                        <i class="ki-duotone {{ $icon }} fs-2"></i>
                                    </div>
                                </div>
                                
                                {{-- Content --}}
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="text-gray-900 fw-bold mb-1">{{ $data['title'] ?? 'Notifikasi Sistem' }}</h5>
                                        <span class="badge badge-light-secondary fs-7">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                    
                                    <p class="text-gray-700 fs-6 mb-3">{{ $data['message'] ?? '' }}</p>
                                    
                                    <div class="d-flex align-items-center gap-3">
                                        @if(isset($data['action_url']))
                                            <a href="{{ $data['action_url'] }}" class="btn btn-sm btn-light-primary">
                                                <i class="ki-duotone ki-eye fs-4"></i>
                                                Buka Dokumen
                                            </a>
                                        @endif
                                        
                                        @if($isUnread)
                                            <a href="{{ route('web.notifications.read', $notification->id) }}" 
                                               class="btn btn-sm btn-light">
                                                <i class="ki-duotone ki-check fs-4"></i>
                                                Tandai Dibaca
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Unread Indicator --}}
                                @if($isUnread)
                                    <div class="w-10px h-10px rounded-circle bg-primary"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="card">
                        <div class="card-body text-center py-15">
                            <div class="d-flex flex-column align-items-center">
                                <i class="ki-duotone ki-notification-bing fs-3x text-gray-400 mb-5"></i>
                                <h3 class="text-gray-700 fs-3 fw-bold mb-2">Belum ada notifikasi</h3>
                                <p class="text-gray-500 fs-6">Seluruh pesan dan update aktivitas sistem akan muncul di sini.</p>
                            </div>
                        </div>
                    </div>
                @endforelse

                {{-- Pagination --}}
                @if($notifications->hasPages())
                    <div class="d-flex flex-stack flex-wrap mt-7">
                        <div class="fs-6 fw-semibold text-gray-700">
                            Menampilkan {{ $notifications->firstItem() }} - {{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi
                        </div>
                        <div>
                            {{ $notifications->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection