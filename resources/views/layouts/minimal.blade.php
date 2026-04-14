<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Medikindo PO System' }} | Medikindo</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/metronic8/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/metronic8/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>
<body id="kt_app_body" data-kt-app-layout="dark-sidebar" data-kt-app-header-fixed="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-hoverable="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            
            <!-- Header -->
            <div id="kt_app_header" class="app-header">
                <div class="app-container container-fluid d-flex align-items-stretch flex-stack" id="kt_app_header_container">
                    <div class="d-flex align-items-center d-block d-lg-none ms-n3">
                        <div class="btn btn-icon btn-active-color-primary w-35px h-35px me-2" id="kt_app_sidebar_mobile_toggle">
                            <i class="ki-duotone ki-abstract-14 fs-2"></i>
                        </div>
                        <a href="{{ route('web.dashboard') }}">
                            <span class="fw-bold text-dark fs-5">Medikindo</span>
                        </a>
                    </div>
                    <div class="app-navbar flex-lg-grow-1" id="kt_app_header_navbar">
                        <div class="app-navbar-item d-flex align-items-stretch flex-lg-grow-1">
                            <div class="d-flex w-lg-200px"></div>
                        </div>
                        <div class="app-navbar-item ms-1 ms-md-3">
                            <a href="#" class="btn btn-icon btn-custom btn-color-gray-600 btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px">
                                <i class="ki-duotone ki-notification fs-1"></i>
                            </a>
                        </div>
                        <div class="app-navbar-item ms-1 ms-md-3">
                            <div class="cursor-pointer symbol symbol-35px symbol-md-40px">
                                <div class="symbol-label fs-5 fw-bold bg-light-primary text-primary">
                                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                
                <!-- Sidebar -->
                <div id="kt_app_sidebar" class="app-sidebar flex-column">
                    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
                        <a href="{{ route('web.dashboard') }}" class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center bg-primary rounded" style="width:36px;height:36px;">
                                <i class="ki-duotone ki-hospital text-white fs-3"></i>
                            </div>
                            <div class="app-sidebar-logo-default d-flex flex-column lh-1">
                                <span class="fw-bold text-dark fs-5 lh-1">Medikindo</span>
                                <span class="text-muted fs-8 fw-semibold">Procurement</span>
                            </div>
                        </a>
                    </div>
                    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
                        <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold">
                            <div class="menu-item">
                                <a class="menu-link active" href="{{ route('web.dashboard') }}">
                                    <span class="menu-icon">
                                        <i class="ki-solid ki-home-2 fs-2"></i>
                                    </span>
                                    <span class="menu-title">Dashboard</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        
                        <!-- Toolbar -->
                        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                            <div class="app-container container-fluid d-flex flex-stack">
                                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                                        {{ $pageTitle ?? 'Test Page' }}
                                    </h1>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('assets/metronic8/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/metronic8/js/scripts.bundle.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof KTApp !== 'undefined' && typeof KTApp.init === 'function') {
                KTApp.init();
            }
        });
    </script>
</body>
</html>