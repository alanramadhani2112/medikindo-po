<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome — Medikindo PO System</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-slate-900 bg-white selection:bg-brand-500 selection:text-white">

    <div class="relative min-h-screen flex flex-col">
        {{-- Navbar --}}
        <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-primary flex items-center justify-center ui-card-title text-white shadow-xl shadow-primary/20">
                            M</div>
                        <span class="text-xl font-bold tracking-tight text-gray-900">Medikindo <span
                                class="text-primary">PO</span></span>
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <a href="{{ route('web.dashboard') }}"
                                class="text-sm font-bold text-slate-600 hover:text-brand-600 transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}"
                                class="px-6 py-2.5 rounded-2xl bg-brand-600 text-white text-sm font-bold shadow-xl shadow-brand-500/20 hover:bg-brand-700 hover:-translate-y-0.5 transition-all duration-300">
                                Masuk ke Sistem
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        {{-- Hero Section --}}
        <main class="flex-grow">
            <div class="relative overflow-hidden pt-20 pb-20 lg:pt-32 lg:pb-32">
                {{-- Decorative Background --}}
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full pointer-events-none -z-10">
                    <div
                        class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-50 rounded-full blur-3xl opacity-50">
                    </div>
                    <div
                        class="absolute bottom-[-10%] right-[-10%] w-[30%] h-[30%] bg-brand-50 rounded-full blur-3xl opacity-50">
                    </div>
                </div>

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <x-badge variant="primary" size="lg" outline class="mb-6 animate-bounce">
                            New: Medikindo OS v2.0
                        </x-badge>

                        <h1 class="text-5xl lg:text-7xl font-bold text-gray-900 tracking-tight mb-8">
                            Manajemen Pengadaan <br />
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-teal-500">Lebih
                                Cerdas & Cepat.</span>
                        </h1>

                        <p class="max-w-2xl mx-auto text-xl text-gray-500 leading-relaxed mb-12">
                            Sistem terintegrasi untuk pengelolaan Purchase Order, Inventaris, dan Laporan Keuangan bagi
                            seluruh jaringan Klinik dan Rumah Sakit Medikindo.
                        </p>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                            @auth
                                <x-button href="{{ route('web.dashboard') }}" size="xl" variant="primary"
                                    class="w-full sm:w-auto">
                                    Buka Dashboard
                                </x-button>
                            @else
                                <x-button href="{{ route('login') }}" size="xl" variant="primary"
                                    class="w-full sm:w-auto px-10">
                                    Mulai Sekarang
                                </x-button>
                                <x-button href="#features" variant="secondary" size="xl" class="w-full sm:w-auto">
                                    Pelajari Fitur
                                </x-button>
                            @endauth
                        </div>

                        {{-- Stats --}}
                        <div class="mt-20 grid grid-cols-2 lg:grid-cols-4 gap-8 border-t border-slate-100 pt-16">
                            @foreach ([['label' => 'Total PO Terbit', 'value' => '1.2k+'], ['label' => 'Klinik Terintegrasi', 'value' => '48'], ['label' => 'Supplier Aktif', 'value' => '150+'], ['label' => 'SLA Persetujuan', 'value' => '< 2 Jam']] as $stat)
                                <div class="flex flex-col items-center">
                                    <span class="text-3xl font-bold text-gray-900 mb-1">{{ $stat['value'] }}</span>
                                    <span class="ui-section-label">{{ $stat['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Features section can be added here if needed --}}
        </main>

        {{-- Footer --}}
        <footer class="bg-slate-50 border-t border-slate-100 py-12">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <p class="text-sm font-medium text-slate-400">
                    &copy; {{ date('Y') }} PT. Mentari Medika Indonesia. All rights reserved.
                </p>
                <div class="mt-4 flex justify-center gap-6 text-xs font-bold text-slate-400 uppercase tracking-widest">
                    <a href="#" class="hover:text-brand-600 transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-brand-600 transition-colors">Support Center</a>
                    <a href="#" class="hover:text-brand-600 transition-colors">Portal Tech</a>
                </div>
            </div>
        </footer>
    </div>

</body>

</html>
