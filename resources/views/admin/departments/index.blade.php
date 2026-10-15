@extends('layouts.admin')

@section('title', 'Master Departemen — SI-TAMU')
@section('header_title', 'Master Departemen')

@section('content')
<div class="space-y-6">
    <x-layout.page-header
        title="Master unit / departemen"
        subtitle="Kelola daftar bidang atau unit tujuan kunjungan tamu."
    >
        <x-slot:actions>
            <x-ui.button :href="route('admin.departments.create')" variant="primary" size="md">
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah unit baru</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Search --}}
    <x-ui.card body-class="p-4">
        <form method="GET" action="{{ route('admin.departments.index') }}" class="flex gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <label for="dept-search" class="sr-only">Cari nama unit atau kode</label>
                <input
                    type="text"
                    id="dept-search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama unit atau kode…"
                    class="w-full min-h-11 pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                >
            </div>
            <button
                type="submit"
                class="min-h-11 px-4 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2"
            >
                Cari
            </button>
            @if(request('q'))
                <a
                    href="{{ route('admin.departments.index') }}"
                    class="inline-flex items-center min-h-11 px-3.5 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs text-slate-600 font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                >
                    Reset
                </a>
            @endif
        </form>
    </x-ui.card>

    {{-- Departments list --}}
    <x-ui.card :flush="true">
        @if ($departments->isEmpty())
            <div class="p-5">
                @if (request('q'))
                    <x-ui.empty-state
                        kind="filtered"
                        title="Tidak ada unit yang cocok"
                        description="Coba ubah kata kunci pencarian atau reset filter."
                    >
                        <x-slot:action>
                            <x-ui.button :href="route('admin.departments.index')" variant="secondary" size="sm">Reset pencarian</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @else
                    <x-ui.empty-state
                        title="Belum ada unit / departemen"
                        description="Tambahkan unit tujuan pertama untuk mulai menerima registrasi tamu."
                    >
                        <x-slot:action>
                            <x-ui.button :href="route('admin.departments.create')" variant="primary" size="sm">Tambah unit baru</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @endif
            </div>
        @else
            <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel unit dan departemen">
                <table class="w-full min-w-[900px] xl:table-fixed text-left text-sm divide-y divide-slate-200">
                    <caption class="sr-only">Daftar unit / departemen tujuan kunjungan</caption>
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500">
                        <tr>
                            <th scope="col" class="w-[7%] px-3.5 py-3">Urutan</th>
                            <th scope="col" class="w-[9%] px-3.5 py-3">Kode</th>
                            <th scope="col" class="w-[17%] px-3.5 py-3">Nama unit</th>
                            <th scope="col" class="w-[25%] px-3.5 py-3">Deskripsi</th>
                            <th scope="col" class="w-[10%] px-3.5 py-3 text-center">Total kunjungan</th>
                            <th scope="col" class="w-[10%] px-3.5 py-3">Status</th>
                            <th scope="col" class="w-[22%] px-3.5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($departments as $dept)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-3.5 py-3 whitespace-nowrap text-xs font-mono text-slate-500">
                                    {{ $dept->sort_order }}
                                </td>
                                <td class="px-3.5 py-3 whitespace-nowrap text-xs font-mono font-bold text-slate-700 truncate" title="{{ $dept->code }}">
                                    {{ $dept->code }}
                                </td>
                                <td class="px-3.5 py-3 text-sm font-medium leading-snug text-slate-900">
                                    {{ $dept->name }}
                                </td>
                                <td class="px-3.5 py-3 text-xs text-slate-500 truncate" title="{{ $dept->description }}">
                                    {{ $dept->description ?: '—' }}
                                </td>
                                <td class="px-3.5 py-3 whitespace-nowrap text-xs text-center font-semibold text-slate-700">
                                    {{ $dept->visits_count }}
                                </td>
                                <td class="px-3.5 py-3 whitespace-nowrap">
                                    @if ($dept->is_active)
                                        <x-ui.badge tone="success" :dot="true">Aktif</x-ui.badge>
                                    @else
                                        <x-ui.badge tone="neutral" :dot="true">Nonaktif</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-3.5 py-3 whitespace-nowrap text-right text-xs">
                                    <div class="inline-flex items-center gap-3">
                                        @if ($dept->is_active)
                                            <x-ui.button
                                                type="button"
                                                variant="dangerSoft"
                                                size="sm"
                                                x-on:click="$dispatch('confirm-action', {
                                                    url: {{ Illuminate\Support\Js::from(route('admin.departments.toggle-status', $dept)) }},
                                                    method: 'PATCH',
                                                    title: 'Nonaktifkan unit ini?',
                                                    body: 'Unit yang dinonaktifkan tidak akan muncul untuk registrasi baru. Riwayat kunjungan lama tetap dipertahankan.',
                                                    confirm: 'Ya, nonaktifkan',
                                                    tone: 'danger',
                                                    trigger: $el,
                                                })"
                                            >Nonaktifkan</x-ui.button>
                                        @else
                                            <form action="{{ route('admin.departments.toggle-status', $dept) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <x-ui.button type="submit" variant="secondary" size="sm">Aktifkan</x-ui.button>
                                            </form>
                                        @endif

                                        <span class="w-px h-5 bg-slate-200" aria-hidden="true"></span>

                                        <a
                                            href="{{ route('admin.departments.edit', $dept) }}"
                                            class="inline-flex items-center min-h-9 px-2.5 py-1.5 rounded-lg bg-primary-50 hover:bg-primary-100 text-primary-700 font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                                        >
                                            Edit
                                        </a>

                                        @if ($dept->visits_count === 0)
                                            <form
                                                action="{{ route('admin.departments.destroy', $dept) }}"
                                                method="POST"
                                                onsubmit="return confirm(@js('Hapus unit '.$dept->name.'? Tindakan ini tidak dapat dibatalkan.'))"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    aria-label="Hapus unit {{ $dept->name }}"
                                                    class="inline-flex items-center justify-center min-w-9 min-h-9 p-1.5 rounded-lg text-danger-600 hover:bg-danger-50 hover:text-danger-700 transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-danger-500"
                                                    title="Hapus unit"
                                                >
                                                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    </x-ui.card>
</div>

<x-ui.confirm-modal />
@endsection
