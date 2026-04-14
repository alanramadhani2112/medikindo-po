<!--begin::Header-->
<div id="kt_app_header" class="app-header">
    <!--begin::Header container-->
    <div class="app-container container-fluid d-flex align-items-stretch flex-stack" id="kt_app_header_container">
        <!--begin::Sidebar toggle-->
        <div class="d-flex align-items-center d-block d-lg-none ms-n3" title="Show sidebar menu">
            <div class="btn btn-icon btn-active-color-primary w-35px h-35px me-2" id="kt_app_sidebar_mobile_toggle">
                <i class="ki-solid ki-abstract-14 fs-2"></i>
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
                <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px position-relative">
                    @php $notifCount = auth()->user()?->unreadNotifications()->count() ?? 0; @endphp
                    <a href="{{ route('web.notifications.index') }}" class="text-gray-500">
                        <i class="ki-solid ki-notification-bing fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        @if($notifCount > 0)
                            <span class="bullet bullet-dot bg-danger h-6px w-6px position-absolute translate-middle top-0 start-50 animation-blink"></span>
                        @endif
                    </a>
                </div>
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
                                <i class="ki-solid ki-exit-right fs-3 me-2"></i>
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
