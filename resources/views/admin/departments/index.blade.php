@extends('layouts.admin')

@section('title', 'Master Departemen — SI-TAMU')
@section('header_title', 'Master Departemen')

@section('content')
<div class="space-y-6">
    <!-- Header Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Master Unit / Departemen</h1>
            <p class="text-xs sm:text-sm text-slate-500">
                Kelola daftar bidang atau unit tujuan kunjungan tamu.
            </p>
        </div>
        <div>
            <a 
                href="{{ route('admin.departments.create') }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah Unit Baru</span>
            </a>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.departments.index') }}" class="flex gap-3">
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
                    placeholder="Cari nama unit atau kode..." 
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
            </div>
            <button 
                type="submit" 
                class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition"
            >
                Cari
            </button>
            @if(request('q'))
                <a 
                    href="{{ route('admin.departments.index') }}" 
                    class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs text-slate-600 font-medium transition flex items-center"
                >
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Departments Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        @if ($departments->isEmpty())
            <div class="p-12 text-center space-y-2">
                <p class="text-sm font-semibold text-slate-700">Belum ada unit / departemen.</p>
                <p class="text-xs text-slate-500">Tambahkan unit tujuan pertama untuk mulai menerima registrasi tamu.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-slate-200">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th scope="col" class="px-5 py-3">Urutan</th>
                            <th scope="col" class="px-5 py-3">Kode</th>
                            <th scope="col" class="px-5 py-3">Nama Unit</th>
                            <th scope="col" class="px-5 py-3">Deskripsi</th>
                            <th scope="col" class="px-5 py-3 text-center">Total Kunjungan</th>
                            <th scope="col" class="px-5 py-3">Status</th>
                            <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($departments as $dept)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono text-slate-500">
                                    {{ $dept->sort_order }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs font-mono font-bold text-slate-700">
                                    {{ $dept->code }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-sm font-semibold text-slate-900">
                                    {{ $dept->name }}
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-500 max-w-xs truncate">
                                    {{ $dept->description ?: '—' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-xs text-center font-semibold text-slate-700">
                                    {{ $dept->visits_count }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if ($dept->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs">
                                    <div class="inline-flex items-center gap-1.5">
                                        <form action="{{ route('admin.departments.toggle-status', $dept) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button 
                                                type="submit" 
                                                class="px-2.5 py-1 rounded-md text-xs font-medium border border-slate-200 hover:bg-slate-50 text-slate-700 transition"
                                                title="{{ $dept->is_active ? 'Nonaktifkan unit' : 'Aktifkan unit' }}"
                                            >
                                                {{ $dept->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <a 
                                            href="{{ route('admin.departments.edit', $dept) }}" 
                                            class="px-2.5 py-1 rounded-md bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium transition"
                                        >
                                            Edit
                                        </a>

                                        @if ($dept->visits_count === 0)
                                            <form 
                                                action="{{ route('admin.departments.destroy', $dept) }}" 
                                                method="POST" 
                                                onsubmit="return confirm('Hapus unit {{ $dept->name }}? Tindakan ini tidak dapat dibatalkan.')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    class="p-1 rounded-md text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition"
                                                    title="Hapus unit"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
