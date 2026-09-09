<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SI-TAMU'))</title>

    <!-- Styles & Scripts via Vite (Zero External CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-800 selection:bg-indigo-500 selection:text-white">
    <!-- Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('public.registration.create') }}" class="flex items-center gap-3 group focus:outline-hidden focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-lg">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-xs font-bold text-lg group-hover:bg-indigo-700 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-semibold text-slate-900 leading-tight">
                        {{ $organization?->organization_name ?? 'SI-TAMU' }}
                    </h1>
                    <p class="text-xs text-slate-500">Buku Tamu Digital</p>
                </div>
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="text-xs font-medium text-slate-600 hover:text-indigo-600 transition px-2.5 py-1.5 rounded-md hover:bg-slate-100 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    <span>Akses Petugas</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 py-8 sm:py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl mx-auto">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
        <div class="max-w-4xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} {{ $organization?->organization_name ?? 'SI-TAMU' }}. Seluruh hak cipta dilindungi.</p>
            <p class="mt-1 text-slate-400">Sistem Informasi Buku Tamu Digital</p>
        </div>
    </footer>
</body>
</html>
