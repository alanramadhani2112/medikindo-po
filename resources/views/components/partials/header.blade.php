<!--begin::Header-->
<div id="kt_app_header" class="app-header">
    <!--begin::Header container-->
    <div class="app-container container-fluid d-flex align-items-stretch flex-stack" id="kt_app_header_container">
        <!--begin::Sidebar toggle-->
        <div class="d-flex align-items-center d-block d-lg-none ms-n3" title="Show sidebar menu">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px me-2" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-outline ki-abstract-14 fs-2"></i>
            </div>
        </div>
        <!--end::Sidebar toggle-->
        
        <!--begin::Navbar-->
        <div class="app-navbar flex-lg-grow-1" id="kt_app_header_navbar">
            <div class="app-navbar-item d-flex align-items-stretch flex-lg-grow-1">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-gray-900 fw-bold fs-3 flex-column justify-content-center my-0">{{ $pageTitle ?? 'Dashboard' }}</h1>
                    <!--end::Title-->
                    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('web.dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        @foreach($breadcrumbs as $breadcrumb)
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-500 w-5px h-2px"></span>
                            </li>
                            @php
                                $title = $breadcrumb['title'] ?? $breadcrumb['label'] ?? $breadcrumb['name'] ?? '';
                                $url = $breadcrumb['url'] ?? null;
                            @endphp
                            <li class="breadcrumb-item text-muted">
                                @if($url)
                                    <a href="{{ $url }}" class="text-muted text-hover-primary">{{ $title }}</a>
                                @else
                                    <span class="text-gray-700">{{ $title }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <!--end::Breadcrumb-->
                    @endif
                </div>
                <!--end::Page title-->
            </div>
            
            <!--begin::Navbar items-->
            <div class="app-navbar-item ms-1 ms-md-3">
                <!--begin::Notifications-->
                @php $notifCount = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp
                <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-40px h-40px position-relative" 
                     data-kt-menu-trigger="{default: 'click', lg: 'hover'}" 
                     data-kt-menu-attach="parent" 
                     data-kt-menu-placement="bottom-end"
                     id="kt_notification_toggle">
                    <i class="ki-outline ki-notification-bing fs-1"></i>
                    @if($notifCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge badge-sm badge-circle badge-danger" 
                              style="font-size: 9px; min-width: 18px; height: 18px; padding: 0; display: flex; align-items: center; justify-content: center;">
                            {{ $notifCount > 99 ? '99+' : $notifCount }}
                        </span>
                    @endif
                </div>
                
                <!--begin::Notification dropdown-->
                <div class="menu menu-sub menu-sub-dropdown menu-column w-350px w-lg-425px" data-kt-menu="true" id="kt_notification_menu">
                    <!--begin::Header-->
                    <div class="d-flex flex-column bgi-no-repeat rounded-top" 
                         style="background-image:url('{{ asset('assets/metronic8/media/misc/menu-header-bg.jpg') }}'); background-size: cover; background-position: center;">
                        <div class="px-9 pt-7 pb-5">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h3 class="text-white fw-bold mb-0 fs-2">Notifikasi</h3>
                                @if($notifCount > 0)
                                    <span class="badge badge-light-danger badge-circle fw-bold fs-7" style="min-width: 24px; height: 24px; padding: 0 8px;">
                                        {{ $notifCount }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-white opacity-75 fs-7 fw-semibold">
                                {{ $notifCount > 0 ? "Anda memiliki {$notifCount} notifikasi belum dibaca" : "Tidak ada notifikasi baru" }}
                            </span>
                        </div>
                    </div>
                    <!--end::Header-->
                    
                    <!--begin::Items-->
                    <div class="scroll-y mh-350px px-5 py-5" id="kt_notification_items">
                        @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                            @php
                                $isUnread = is_null($notification->read_at);
                                $data = $notification->data;
                                $icon = match($data['type'] ?? 'default') {
                                    'po_submitted' => 'document',
                                    'po_approved' => 'check-circle',
                                    'po_rejected' => 'cross-circle',
                                    'goods_receipt' => 'package',
                                    'invoice' => 'bill',
                                    'payment' => 'wallet',
                                    default => 'notification-bing',
                                };
                                $iconColor = match($data['type'] ?? 'default') {
                                    'po_approved', 'goods_receipt' => 'success',
                                    'po_rejected' => 'danger',
                                    'po_submitted' => 'warning',
                                    'invoice', 'payment' => 'info',
                                    default => 'primary',
                                };
                            @endphp
                            
                            <a href="{{ route('web.notifications.markAsRead', $notification->id) }}" 
                               class="d-flex align-items-start text-hover-light-primary p-4 rounded mb-2 {{ $isUnread ? 'bg-light-primary' : 'bg-hover-light' }}">
                                <!--begin::Icon-->
                                <div class="symbol symbol-45px me-4 flex-shrink-0">
                                    <span class="symbol-label bg-light-{{ $iconColor }}">
                                        <i class="ki-outline ki-{{ $icon }} fs-2 text-{{ $iconColor }}"></i>
                                    </span>
                                </div>
                                <!--end::Icon-->
                                
                                <!--begin::Content-->
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="text-gray-900 fw-bold fs-6 d-block mb-1">
                                            {{ $data['title'] ?? 'Notifikasi' }}
                                        </span>
                                        @if($isUnread)
                                            <span class="badge badge-primary badge-sm ms-2 flex-shrink-0">Baru</span>
                                        @endif
                                    </div>
                                    <span class="text-gray-700 fs-7 d-block mb-2 text-truncate-2-lines">
                                        {{ $data['message'] ?? '' }}
                                    </span>
                                    <div class="d-flex align-items-center">
                                        <i class="ki-outline ki-time fs-7 text-gray-500 me-1"></i>
                                        <span class="text-gray-500 fs-8">{{ $notification->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <!--end::Content-->
                            </a>
                            
                            @if(!$loop->last)
                                <div class="separator separator-dashed my-2"></div>
                            @endif
                        @empty
                            <div class="d-flex flex-column align-items-center text-center py-10 px-5">
                                <div class="symbol symbol-100px mb-5">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-outline ki-notification-status fs-3x text-primary"></i>
                                    </div>
                                </div>
                                <div class="text-gray-900 fw-bold fs-5 mb-2">Tidak ada notifikasi</div>
                                <div class="text-gray-600 fs-7">Anda akan menerima notifikasi di sini</div>
                            </div>
                        @endforelse
                    </div>
                    <!--end::Items-->
                    
                    <!--begin::Footer-->
                    @if(auth()->user()->notifications()->count() > 0)
                        <div class="py-4 text-center border-top">
                            <a href="{{ route('web.notifications.index') }}" class="btn btn-sm btn-color-gray-700 btn-active-color-primary fw-bold">
                                Lihat Semua Notifikasi
                                <i class="ki-outline ki-arrow-right fs-5 ms-1"></i>
                            </a>
                        </div>
                    @endif
                    <!--end::Footer-->
                </div>
                <!--end::Notification dropdown-->
                <!--end::Notifications-->
            </div>
            
            <!--begin::User menu-->
            <div class="app-navbar-item ms-1 ms-md-3" id="kt_header_user_menu_toggle">
                <div class="cursor-pointer symbol symbol-35px symbol-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                    <div class="symbol-label fs-5 fw-semibold bg-light-primary text-primary">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                    </div>
                </div>
                <!--begin::User account menu-->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-300px" data-kt-menu="true">
                    <!--begin::Menu item-->
                    <div class="menu-item px-3">
                        <div class="menu-content d-flex align-items-center px-3 py-3">
                            <!--begin::Avatar-->
                            <div class="symbol symbol-45px me-4">
                                <div class="symbol-label fs-5 fw-bold bg-light-primary text-primary">
                                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                            <!--end::Avatar-->
                            <!--begin::Info-->
                            <div class="d-flex flex-column flex-grow-1">
                                <div class="fw-bold fs-6 text-gray-900 mb-1">{{ auth()->user()?->name }}</div>
                                <div class="fw-semibold text-muted fs-7 mb-1">{{ auth()->user()?->email }}</div>
                                <span class="badge badge-light-primary fw-semibold fs-8 px-2 py-1 mt-1 align-self-start">
                                    {{ auth()->user()?->roles->first()?->name ?? 'User' }}
                                </span>
                            </div>
                            <!--end::Info-->
                        </div>
                    </div>
                    <!--end::Menu item-->
                    
                    <!--begin::Menu separator-->
                    <div class="separator my-2"></div>
                    <!--end::Menu separator-->
                    
                    <!--begin::Menu item-->
                    <div class="menu-item px-5">
                        <form method="POST" action="{{ route('logout') }}" class="w-100">
                            @csrf
                            <button type="submit" class="btn btn-light-danger btn-sm w-100 d-flex align-items-center justify-content-center">
                                <i class="ki-outline ki-exit-right fs-3 me-2"></i>
                                <span class="fw-bold">Keluar</span>
                            </button>
                        </form>
                    </div>
                    <!--end::Menu item-->
                </div>
                <!--end::User account menu-->
            </div>
            <!--end::User menu-->
            <!--end::Navbar items-->
        </div>
        <!--end::Navbar-->
    </div>
    <!--end::Header container-->
</div>
<!--end::Header-->
