@extends('layouts.admin')

@section('title', 'Manajemen Pengguna — SI-TAMU')
@section('header_title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">
    <!-- Header Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Manajemen Pengguna Back-Office</h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Kelola akun petugas front-office dan administrator sistem.
            </p>
        </div>
        <div>
            <a 
                href="{{ route('admin.users.create') }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Pengguna</span>
            </a>
        </div>
    </div>

    <!-- Search & Role Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    name="q" 
                    value="{{ request('q') }}" 
                    placeholder="Cari nama atau email pengguna..." 
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <div class="w-full sm:w-48">
                <select 
                    name="role" 
                    class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Semua Peran</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->slug }}" {{ request('role') === $r->slug ? 'selected' : '' }}>
                            {{ $r->name }} ({{ $r->slug }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    type="submit" 
                    class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition"
                >
                    Filter
                </button>
                @if(request()->hasAny(['q', 'role']))
                    <a 
                        href="{{ route('admin.users.index') }}" 
                        class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs text-slate-600 font-medium transition"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if ($users->isEmpty())
            <div class="p-12 text-center space-y-2">
                <p class="text-sm font-semibold text-slate-700">Tidak ada pengguna yang sesuai.</p>
                <p class="text-xs text-slate-500">Coba ubah kriteria pencarian atau reset filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3">Pengguna</th>
                            <th scope="col" class="px-5 py-3">Peran</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3">Login Terakhir</th>
                            <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($users as $u)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 text-sm flex items-center gap-2">
                                        <span>{{ $u->name }}</span>
                                        @if ($u->id === auth()->id())
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700">
                                                Anda
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $u->email }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @php
                                        $primaryRole = $u->primary_role_code?->value ?? 'STAFF';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $primaryRole === 'ADMIN' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                        {{ $primaryRole }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($u->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-500">
                                    {{ $u->last_login_at ? $u->last_login_at->format('d M Y, H:i') : 'Belum pernah' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs">
                                    <div class="inline-flex items-center gap-1.5">
                                        @if ($u->id !== auth()->id())
                                            <form action="{{ route('admin.users.toggle-status', $u) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button 
                                                    type="submit" 
                                                    class="px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 hover:bg-slate-50 text-slate-700 transition"
                                                >
                                                    {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @endif

                                        <a 
                                            href="{{ route('admin.users.edit', $u) }}" 
                                            class="px-2.5 py-1 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium transition"
                                        >
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
