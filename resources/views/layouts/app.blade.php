<!DOCTYPE html>
<html lang="en">
<head>
    <base href=""/>
    <title>{{ $title ?? 'Medikindo PO System' }} | Medikindo</title>
    <meta charset="utf-8" />
    <meta name="description" content="Medikindo Procurement & Financial System" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/metronic8/media/logos/favicon.ico') }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/metronic8/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/metronic8/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/custom-layout.css') }}" rel="stylesheet" type="text/css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body id="kt_app_body" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-push-header="true" data-kt-app-sidebar-push-toolbar="true" data-kt-app-sidebar-push-footer="true" data-kt-app-toolbar-enabled="true" class="app-default">
    <script>
        var defaultThemeMode = "light"; 
        var themeMode; 
        if (document.documentElement) { 
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) { 
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); 
            } else { 
                if (localStorage.getItem("data-bs-theme") !== null) { 
                    themeMode = localStorage.getItem("data-bs-theme"); 
                } else { 
                    themeMode = defaultThemeMode; 
                } 
            } 
            if (themeMode === "system") { 
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; 
            } 
            document.documentElement.setAttribute("data-bs-theme", themeMode); 
        }
    </script>
    <div class="d-flex flex-column flex-root app-root" id="kt_app_root">
        <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
            @include('components.partials.header')
            <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
                @include('components.partials.sidebar')
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                    <div class="d-flex flex-column flex-column-fluid">
                        <div id="kt_app_content" class="app-content flex-column-fluid">
                            <div id="kt_app_content_container" class="app-container container-fluid">
                                @if(session('success'))
                                    <div class="alert alert-success d-flex align-items-center mb-5">
                                        <i class="ki-solid ki-check-circle fs-2 text-success me-3"></i>
                                        <span>{{ session('success') }}</span>
                                    </div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger d-flex align-items-center mb-5">
                                        <i class="ki-solid ki-cross-circle fs-2 text-danger me-3"></i>
                                        <span>{{ session('error') }}</span>
                                    </div>
                                @endif
                                @if($errors->any())
                                    <div class="alert alert-danger d-flex align-items-start mb-5">
                                        <i class="ki-solid ki-information-5 fs-2 text-danger me-3 mt-1"></i>
                                        <div>
                                            <div class="fw-bold mb-1">Terdapat kesalahan validasi:</div>
                                            <ul class="mb-0 ps-4">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                                
                                {{-- Content from views --}}
                                @yield('content')
                            </div>
                        </div>
                    </div>
                    <div id="kt_app_footer" class="app-footer">
                        <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
                            <div class="text-dark order-2 order-md-1">
                                <span class="text-muted fw-semibold me-1">{{ date('Y') }}&copy;</span>
                                <span class="text-gray-800 fw-semibold">Medikindo Procurement System</span>
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
        // Initialize Metronic components when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize KTApp if available
            if (typeof KTApp !== 'undefined' && typeof KTApp.init === 'function') {
                KTApp.init();
            }
            
            // Initialize menu components
            if (typeof KTMenu !== 'undefined') {
                KTMenu.createInstances();
            }
            
            // Initialize drawer components
            if (typeof KTDrawer !== 'undefined') {
                KTDrawer.createInstances();
            }
            
            // Initialize scroll components
            if (typeof KTScroll !== 'undefined') {
                KTScroll.createInstances();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
