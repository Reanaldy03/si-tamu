@php
    $waitingCount = \App\Models\Visit::query()
        ->whereIn('current_status', array_map(fn ($s) => $s->value, \App\Domain\Visits\Enums\VisitStatus::awaitingOperator()))
        ->count();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin — SI-TAMU')</title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Styles & Scripts via Vite (Zero External CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="min-h-full flex flex-col font-sans antialiased text-slate-800 selection:bg-primary-500 selection:text-white"
    x-data="{
        sidebarOpen: false,
        isDesktop: window.matchMedia('(min-width: 1024px)').matches,
        init() {
            window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => {
                this.isDesktop = e.matches;
                if (e.matches) this.sidebarOpen = false;
            });
        },
    }"
    x-effect="document.body.classList.toggle('overflow-hidden', sidebarOpen)"
    @keydown.escape.window="if (sidebarOpen) { sidebarOpen = false; $nextTick(() => requestAnimationFrame(() => $refs.navToggle?.focus())); }"
>
    <a
        href="#main-content"
        class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-lg focus:bg-primary-600 focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white focus:shadow-lg focus:outline-hidden focus-visible:ring-2 focus-visible:ring-white"
    >Lewati ke konten utama</a>

    <div class="flex min-h-screen">
        <!-- Mobile Sidebar Backdrop -->
        <div 
            x-show="sidebarOpen" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden" aria-hidden="true"
            @click="sidebarOpen = false; $nextTick(() => requestAnimationFrame(() => $refs.navToggle?.focus()))"
            x-cloak
        ></div>

        <!-- Sidebar Navigation (Desktop & Mobile Drawer) -->
        <aside
            id="admin-sidebar"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            :inert="!sidebarOpen && !isDesktop"
            x-effect="if (sidebarOpen && !isDesktop) $nextTick(() => requestAnimationFrame(() => $refs.drawerClose?.focus()))"
            class="fixed inset-y-0 left-0 z-50 w-64 -translate-x-full bg-slate-900 text-slate-300 flex flex-col overflow-y-auto transition-transform duration-300 ease-in-out lg:translate-x-0 lg:sticky lg:top-0 lg:bottom-auto lg:h-screen shadow-xl lg:shadow-none"
        >
            <!-- Brand / Org Header -->
            <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 bg-slate-950/50">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 min-h-11 group rounded-lg focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-400">
                    <img src="{{ asset('assets/logo.webp') }}" alt="Logo DPRD Kota Binjai" class="h-8 w-auto object-contain shrink-0 drop-shadow-xs">
                    <div>
                        <span class="text-sm font-bold text-white tracking-wide block leading-tight">SI-TAMU</span>
                        <span class="text-[11px] text-slate-400 block truncate max-w-[140px]">
                            {{ \App\Models\OrganizationSetting::current()?->organization_name ?? 'Back-Office' }}
                        </span>
                    </div>
                </a>
                <button 
                    type="button" 
                    x-ref="drawerClose"
                    @click="sidebarOpen = false; $nextTick(() => requestAnimationFrame(() => $refs.navToggle?.focus()))"
                    class="lg:hidden -mr-1 p-3 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-400"
                    aria-label="Tutup navigasi"
                >
                    <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav aria-label="Navigasi utama" class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
                <!-- Operational Group -->
                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold text-slate-400">Operasional</p>
                    
                    <!-- Dashboard -->
                    <a 
                        href="{{ route('admin.dashboard') }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ request()->routeIs('admin.dashboard') ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Visits / Kunjungan -->
                    <a 
                        href="{{ route('admin.visits.index') }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ request()->routeIs('admin.visits.*') ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    @if(request()->routeIs('admin.visits.*')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.visits.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Data Kunjungan</span>
                        @if ($waitingCount > 0)
                            <span
                                class="ml-auto inline-flex shrink-0 items-center justify-center min-w-6 h-6 px-1.5 rounded-full text-xs font-semibold tabular {{ request()->routeIs('admin.visits.*') ? 'bg-white text-primary-700' : 'bg-slate-800 text-slate-200' }}"
                                aria-label="{{ $waitingCount }} kunjungan menunggu tindakan"
                            >
                                <span aria-hidden="true">{{ $waitingCount }}</span>
                            </span>
                        @endif
                    </a>

                    <!-- Pindai QR / Check-In -->
                    @can('checkIn', \App\Models\Visit::class)
                    <a 
                        href="{{ route('admin.checkin.scan') }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ request()->routeIs('admin.checkin.*') ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    @if(request()->routeIs('admin.checkin.*')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.checkin.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <span>Pindai QR / Check-In</span>
                    </a>
                    @endcan

                    <!-- Survei Kepuasan (IKM) -->
                    <a 
                        href="{{ route('admin.feedback.index') }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ request()->routeIs('admin.feedback.*') ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    @if(request()->routeIs('admin.feedback.*')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.feedback.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                        </svg>
                        <span>Survei & Ulasan</span>
                    </a>

                    <!-- Reports (Sprint 8) -->
                    @can('viewAny', \App\Models\ReportExport::class)
                    <a 
                        href="{{ Route::has('admin.reports.index') ? route('admin.reports.index') : '#' }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ Route::has('admin.reports.index') && request()->routeIs('admin.reports.*') ? 'bg-primary-600 text-white shadow-xs' : '' }}"
                    @if(request()->routeIs('admin.reports.*')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.reports.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Laporan & Ekspor</span>
                    </a>
                    @endcan
                </div>

                <!-- Admin Privileges Group -->
                @can('manage', \App\Models\Department::class)
                <div class="space-y-1">
                    <p class="px-3 text-xs font-semibold text-slate-400">Administrasi</p>
                    
                    <!-- Departments (Sprint 7) -->
                    <a 
                        href="{{ Route::has('admin.departments.index') ? route('admin.departments.index') : '#' }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ Route::has('admin.departments.index') && request()->routeIs('admin.departments.*') ? 'bg-primary-600 text-white shadow-xs' : '' }}"
                    @if(request()->routeIs('admin.departments.*')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.departments.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Master Departemen</span>
                    </a>

                    <!-- Users (Sprint 7) -->
                    <a 
                        href="{{ Route::has('admin.users.index') ? route('admin.users.index') : '#' }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ Route::has('admin.users.index') && request()->routeIs('admin.users.*') ? 'bg-primary-600 text-white shadow-xs' : '' }}"
                    @if(request()->routeIs('admin.users.*')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Manajemen User</span>
                    </a>

                    <!-- Audit Log (Sprint 7) -->
                    <a 
                        href="{{ Route::has('admin.audits.index') ? route('admin.audits.index') : '#' }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ Route::has('admin.audits.index') && request()->routeIs('admin.audits.*') ? 'bg-primary-600 text-white shadow-xs' : '' }}"
                    @if(request()->routeIs('admin.audits.*')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.audits.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Audit Log</span>
                    </a>

                    <!-- Settings (Sprint 7) -->
                    <a 
                        href="{{ route('admin.settings.edit') }}" 
                        class="flex items-center gap-3 min-h-11 px-3 py-2.5 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white focus:outline-hidden focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary-400 {{ request()->routeIs('admin.settings.*') ? 'bg-primary-600 text-white shadow-xs' : '' }}"
                    @if(request()->routeIs('admin.settings.*')) aria-current="page" @endif
                    >
                        <svg aria-hidden="true" class="w-5 h-5 {{ request()->routeIs('admin.settings.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Pengaturan Sistem</span>
                    </a>
                </div>
                @endcan
            </nav>

            <!-- User Info & Logout Footer -->
            <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                <div class="flex items-center justify-between gap-3 p-2 rounded-lg bg-slate-900">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-slate-800 text-primary-400 font-bold flex items-center justify-center text-xs shrink-0 border border-slate-700">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-white truncate" title="{{ auth()->user()?->name }}">{{ auth()->user()?->name }}</p>
                            <span class="inline-block whitespace-nowrap px-1.5 py-0.5 rounded text-[10px] font-medium bg-primary-900/60 text-primary-300 border border-primary-700/50">
                                {{ auth()->user()?->primary_role_code?->label() ?? 'Staf' }}
                            </span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button 
                            type="submit"
                            aria-label="Keluar dari akun"
                            title="Keluar"
                            class="p-3 -mr-1 rounded-lg text-slate-400 hover:text-danger-400 hover:bg-slate-800 transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-danger-400"
                        >
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0" :inert="sidebarOpen && !isDesktop">
            <!-- Topbar Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-3 min-w-0">
                    <button 
                        type="button"
                        x-ref="navToggle"
                        @click="sidebarOpen = !sidebarOpen"
                        :aria-expanded="sidebarOpen ? 'true' : 'false'"
                        aria-controls="admin-sidebar"
                        class="lg:hidden -ml-1 p-3 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                        :aria-label="sidebarOpen ? 'Tutup navigasi' : 'Buka navigasi'"
                    >
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <p class="min-w-0 truncate text-sm font-semibold text-slate-900">
                        @yield('header_title', 'Back-Office')
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <a 
                        href="{{ route('public.registration.create') }}" 
                        target="_blank"
                        rel="noopener noreferrer"
                        class="hidden sm:inline-flex items-center gap-1.5 min-h-11 text-xs font-medium text-slate-500 hover:text-primary-600 transition px-2 rounded-lg focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                    >
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Buka Form Tamu</span>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main id="main-content" tabindex="-1" class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6 focus:outline-hidden">
                <!-- Flash Notification Alerts -->
                @if (session('success'))
                    <x-ui.alert tone="success" focusable>{{ session('success') }}</x-ui.alert>
                @endif

                @if (session('error'))
                    <x-ui.alert tone="danger">{{ session('error') }}</x-ui.alert>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
