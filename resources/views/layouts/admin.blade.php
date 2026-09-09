<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin — SI-TAMU')</title>

    <!-- Styles & Scripts via Vite (Zero External CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-800 selection:bg-indigo-500 selection:text-white" x-data="{ sidebarOpen: false }">
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
            class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden"
            @click="sidebarOpen = false"
            x-cloak
        ></div>

        <!-- Sidebar Navigation (Desktop & Mobile Drawer) -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 shadow-xl lg:shadow-none"
        >
            <!-- Brand / Org Header -->
            <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 bg-slate-950/50">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-sm font-bold text-white tracking-wide block leading-tight">SI-TAMU</span>
                        <span class="text-[11px] text-slate-400 block truncate max-w-[140px]">
                            {{ \App\Models\OrganizationSetting::current()?->organization_name ?? 'Back-Office' }}
                        </span>
                    </div>
                </a>
                <button 
                    type="button" 
                    @click="sidebarOpen = false" 
                    class="lg:hidden p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-slate-800 transition"
                    aria-label="Tutup navigasi"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
                <!-- Operational Group -->
                <div class="space-y-1">
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Operasional</p>
                    
                    <!-- Dashboard -->
                    <a 
                        href="{{ route('admin.dashboard') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- Visits / Kunjungan -->
                    <a 
                        href="{{ route('admin.visits.index') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.visits.*') ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        <svg class="w-5 h-5 {{ request()->routeIs('admin.visits.*') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Data Kunjungan</span>
                    </a>

                    <!-- Reports (Sprint 8) -->
                    @can('viewAny', \App\Models\ReportExport::class)
                    <a 
                        href="{{ Route::has('admin.reports.index') ? route('admin.reports.index') : '#' }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white {{ Route::has('admin.reports.index') && request()->routeIs('admin.reports.*') ? 'bg-indigo-600 text-white' : '' }}"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Laporan & Ekspor</span>
                    </a>
                    @endcan
                </div>

                <!-- Admin Privileges Group -->
                @can('manage', \App\Models\Department::class)
                <div class="space-y-1">
                    <p class="px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">Administrasi</p>
                    
                    <!-- Departments (Sprint 7) -->
                    <a 
                        href="{{ Route::has('admin.departments.index') ? route('admin.departments.index') : '#' }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white {{ Route::has('admin.departments.index') && request()->routeIs('admin.departments.*') ? 'bg-indigo-600 text-white' : '' }}"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>Master Departemen</span>
                    </a>

                    <!-- Users (Sprint 7) -->
                    <a 
                        href="{{ Route::has('admin.users.index') ? route('admin.users.index') : '#' }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white {{ Route::has('admin.users.index') && request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : '' }}"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Manajemen User</span>
                    </a>

                    <!-- Audit Log (Sprint 7) -->
                    <a 
                        href="{{ Route::has('admin.audits.index') ? route('admin.audits.index') : '#' }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white {{ Route::has('admin.audits.index') && request()->routeIs('admin.audits.*') ? 'bg-indigo-600 text-white' : '' }}"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Audit Log</span>
                    </a>

                    <!-- Settings (Sprint 7) -->
                    <a 
                        href="{{ Route::has('admin.settings.index') ? route('admin.settings.index') : '#' }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition text-slate-300 hover:bg-slate-800 hover:text-white {{ Route::has('admin.settings.index') && request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white' : '' }}"
                    >
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <div class="w-8 h-8 rounded-full bg-slate-800 text-indigo-400 font-bold flex items-center justify-center text-xs shrink-0 border border-slate-700">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()?->name }}</p>
                            <span class="inline-block px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-900/60 text-indigo-300 border border-indigo-700/50">
                                {{ auth()->user()?->primary_role_code?->value ?? 'STAFF' }}
                            </span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button 
                            type="submit" 
                            title="Keluar" 
                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 sticky top-0 z-30 shadow-xs">
                <div class="flex items-center gap-3">
                    <button 
                        type="button" 
                        @click="sidebarOpen = true" 
                        class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition"
                        aria-label="Buka navigasi"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <h2 class="text-base sm:text-lg font-semibold text-slate-900">
                        @yield('header_title', 'Back-Office')
                    </h2>
                </div>

                <div class="flex items-center gap-3">
                    <a 
                        href="{{ route('public.registration.create') }}" 
                        target="_blank" 
                        class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 hover:text-indigo-600 transition px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-indigo-200"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Buka Form Tamu</span>
                    </a>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
                <!-- Flash Notification Alerts -->
                @if (session('success'))
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
