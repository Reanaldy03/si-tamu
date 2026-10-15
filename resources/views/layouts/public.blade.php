<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SI-TAMU'))</title>
    <meta name="description" content="@yield('description', 'Formulir buku tamu digital Sekretariat DPRD Kota Binjai: registrasi kunjungan, QR check-in, dan survei kepuasan layanan.')">

    <!-- Styles & Scripts via Vite (Zero External CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-800 selection:bg-primary-500 selection:text-white">
    <a
        href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded-lg focus:bg-primary-600 focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white focus:shadow-lg focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white"
    >Lewati ke konten utama</a>

    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
            <a href="{{ route('public.registration.create') }}" class="flex min-w-0 items-center gap-2.5 sm:gap-3 min-h-11 group focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500 rounded-xl">
                <img src="{{ asset('assets/logo.webp') }}" alt="" class="h-9 sm:h-10 w-auto object-contain shrink-0 drop-shadow-xs group-hover:scale-105 transition">
                <div class="min-w-0">
                    <h1 class="text-sm sm:text-base font-bold text-slate-900 leading-tight truncate">
                        {{ $organization?->organization_name ?? 'Sekretariat DPRD Kota Binjai' }}
                    </h1>
                    <p class="text-xs text-slate-500 font-medium truncate">Buku Tamu Digital &bull; Kota Binjai</p>
                </div>
            </a>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('login') }}" aria-label="Login" title="Login" class="inline-flex items-center min-h-11 text-xs font-semibold text-slate-700 hover:text-primary-600 transition px-3 py-2.5 rounded-xl bg-slate-100/80 hover:bg-slate-200/70 border border-slate-200 flex items-center gap-1.5 shadow-2xs focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500">
                    <svg aria-hidden="true" class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span class="hidden sm:inline">Login</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main-content" tabindex="-1" class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8 focus:outline-hidden">
        <div class="max-w-2xl mx-auto">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-4xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} {{ $organization?->organization_name ?? 'SI-TAMU' }}. Seluruh hak cipta dilindungi.</p>
            <p class="mt-1 text-slate-500">Sistem Informasi Buku Tamu Digital</p>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
