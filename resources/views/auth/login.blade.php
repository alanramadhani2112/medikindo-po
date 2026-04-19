<!DOCTYPE html>
<html lang="en">
    <!--begin::Head-->
    <head>
        <title>Login System | Medikindo</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/png" />
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
        <link href="/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
        <link href="/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
        <style>
            body { background-image: url('/assets/media/auth/bg4.jpg'); background-size: cover; background-position: center; }
            [data-bs-theme="dark"] body { background-image: url('/assets/media/auth/bg4-dark.jpg'); }
        </style>
    </head>
    <!--end::Head-->
    <!--begin::Body-->
    <body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat">
<script>var defaultThemeMode = "light"; var themeMode; if ( document.documentElement ) { if ( document.documentElement.hasAttribute("data-bs-theme-mode")) { themeMode = document.documentElement.getAttribute("data-bs-theme-mode"); } else { if ( localStorage.getItem("data-bs-theme") !== null ) { themeMode = localStorage.getItem("data-bs-theme"); } else { themeMode = defaultThemeMode; } } if (themeMode === "system") { themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light"; } document.documentElement.setAttribute("data-bs-theme", themeMode); }</script>
        
        <div class="d-flex flex-column flex-root" id="kt_app_root">
            <style>body { background-image: url('/assets/media/auth/bg4.jpg'); } [data-bs-theme="dark"] body { background-image: url('/assets/media/auth/bg4-dark.jpg'); }</style>
            <!--begin::Authentication - Sign-in -->
            <div class="d-flex flex-column flex-column-fluid flex-lg-row">
                <!--begin::Aside-->
                <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
                    <div class="d-flex flex-center flex-lg-start flex-column">
                        <h2 class="text-white fw-normal m-0" style="font-size:3rem">Medikindo</h2>
                        <h1 class="text-white fw-bold mb-7" style="font-size:2rem">Procurement & Financial System</h1>
                        <p class="text-white fw-semibold fs-5 opacity-75 text-center text-lg-start w-75">Sistem terintegrasi untuk mengelola siklus pengadaan klinik dan fasilitas medis.</p>
                    </div>
                </div>
                <!--begin::Aside-->

                <!--begin::Body-->
                <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12 p-lg-20">
                    <!--begin::Card-->
                    <div class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-20">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-center flex-column flex-column-fluid px-lg-10 pb-15 pb-lg-20">
                            <!--begin::Form-->
                            <form class="form w-100" novalidate="novalidate" id="kt_sign_in_form" action="{{ route('login') }}" method="POST">
                                @csrf
                                <!--begin::Heading-->
                                <div class="text-center mb-11">
                                    <h1 class="text-dark fw-bolder mb-3">Login System</h1>
                                    <div class="text-gray-500 fw-semibold fs-6">Masuk untuk melanjutkan</div>
                                </div>
                                <!--begin::Heading-->

                                @if (session('error'))
                                <div class="alert alert-danger d-flex align-items-center p-4 mb-8">
                                    <i class="ki-outline ki-information-5 fs-2hx text-danger me-4"></i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-danger">Akses Ditolak</h4>
                                        <span class="fs-7">{{ session('error') }}</span>
                                    </div>
                                </div>
                                @endif

                                @if ($errors->any())
                                <div class="alert alert-danger d-flex align-items-center p-4 mb-8">
                                    <i class="ki-outline ki-information-5 fs-2hx text-danger me-4"></i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1 text-danger">Validasi Error</h4>
                                        <ul class="mb-0 fs-7">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                @endif

                                <!--begin::Input group=-->
                                <div class="fv-row mb-8">
                                    <input type="email" placeholder="Email Address" name="email" value="{{ old('email') }}" autocomplete="email" class="form-control form-control-solid bg-transparent" required />
                                </div>
                                <!--end::Input group=-->
                                <div class="fv-row mb-8">
                                    <input type="password" placeholder="Masukan Password" name="password" autocomplete="current-password" class="form-control form-control-solid bg-transparent" required />
                                </div>
                                <!--end::Input group=-->

                                <!--begin::Wrapper-->
                                <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-8">
                                    <label class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} />
                                        <span class="form-check-label text-gray-500">Ingat Saya</span>
                                    </label>
                                    @if (Route::has('password.request'))
                                    <a href="#" class="link-primary">Lupa Password?</a>
                                    @endif
                                </div>
                                <!--end::Wrapper-->
                                
                                <!--begin::Submit button-->
                                <div class="d-grid mb-10">
                                    <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                                        <span class="indicator-label">Masuk Sekarang</span>
                                    </button>
                                </div>
                                <!--end::Submit button-->
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Authentication - Sign-in-->
        </div>

        <script src="/assets/plugins/global/plugins.bundle.js"></script>
        <script src="/assets/js/scripts.bundle.js"></script>
    </body>
    <!--end::Body-->
</html>
