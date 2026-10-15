@extends('layouts.admin')

@section('title', 'Manajemen Pengguna — SI-TAMU')
@section('header_title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">
    <x-layout.page-header
        title="Manajemen pengguna back-office"
        subtitle="Kelola akun petugas front-office dan administrator sistem."
    >
        <x-slot:actions>
            <x-ui.button :href="route('admin.users.create')" variant="primary" size="md">
                <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Tambah pengguna</span>
            </x-ui.button>
        </x-slot:actions>
    </x-layout.page-header>

    {{-- Search & role filter --}}
    <x-ui.card body-class="p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <label for="user-search" class="sr-only">Cari nama atau email pengguna</label>
                <input
                    type="text"
                    id="user-search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama atau email pengguna…"
                    class="w-full min-h-11 pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm text-slate-900 placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                >
            </div>

            <div class="w-full sm:w-48">
                <label for="user-role" class="sr-only">Filter berdasarkan peran</label>
                <select
                    id="user-role"
                    name="role"
                    class="w-full min-h-11 px-3 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm bg-white text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-primary-500"
                >
                    <option value="">Semua peran</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->slug }}" {{ request('role') === $r->slug ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button
                    type="submit"
                    class="w-full sm:w-auto justify-center inline-flex items-center min-h-11 px-4 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition cursor-pointer focus:outline-hidden focus-visible:ring-2 focus-visible:ring-slate-900 focus-visible:ring-offset-2"
                >
                    Filter
                </button>
                @if(request()->hasAny(['q', 'role']))
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center min-h-11 px-3.5 py-2.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-xs text-slate-600 font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </x-ui.card>

    {{-- Users list --}}
    <x-ui.card :flush="true">
        @if ($users->isEmpty())
            <div class="p-5">
                @if (request()->hasAny(['q', 'role']))
                    <x-ui.empty-state
                        kind="filtered"
                        title="Tidak ada pengguna yang cocok"
                        description="Coba ubah kata kunci pencarian atau reset filter peran."
                    >
                        <x-slot:action>
                            <x-ui.button :href="route('admin.users.index')" variant="secondary" size="sm">Reset filter</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @else
                    <x-ui.empty-state
                        title="Belum ada pengguna"
                        description="Tambahkan akun petugas atau administrator pertama untuk mulai bekerja."
                    >
                        <x-slot:action>
                            <x-ui.button :href="route('admin.users.create')" variant="primary" size="sm">Tambah pengguna</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                @endif
            </div>
        @else
            {{-- Table: desktop only --}}
            <div class="hidden md:block">
                <div class="overflow-x-auto" tabindex="0" role="region" aria-label="Tabel pengguna">
                    <table class="w-full text-left text-sm divide-y divide-slate-200">
                        <caption class="sr-only">Daftar akun pengguna back-office</caption>
                        <thead class="bg-slate-50 text-[11px] font-semibold text-slate-500">
                            <tr>
                                <th scope="col" class="px-5 py-3">Pengguna</th>
                                <th scope="col" class="px-5 py-3">Peran</th>
                                <th scope="col" class="px-5 py-3">Status</th>
                                <th scope="col" class="px-5 py-3">Login terakhir</th>
                                <th scope="col" class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($users as $u)
                                @php
                                    $role = $u->primary_role_code;
                                    $roleLabel = $role?->label() ?? 'Staf';
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <div class="font-semibold text-slate-900 text-sm flex items-center gap-2">
                                            <span>{{ $u->name }}</span>
                                            @if ($u->id === auth()->id())
                                                <x-ui.badge tone="primary">Anda</x-ui.badge>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500">{{ $u->email }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <x-ui.badge :tone="$role === \App\Domain\Access\Enums\RoleCode::ADMIN ? 'primary' : 'neutral'" :title="$role?->value">{{ $roleLabel }}</x-ui.badge>
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        @if ($u->is_active)
                                            <x-ui.badge tone="success" :dot="true">Aktif</x-ui.badge>
                                        @else
                                            <x-ui.badge tone="danger" :dot="true">Nonaktif</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-500">
                                        {{ $u->last_login_at ? $u->last_login_at->format('d M Y, H:i') : 'Belum pernah' }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap text-right text-xs">
                                        <div class="inline-flex items-center gap-3">
                                            @if ($u->id !== auth()->id())
                                                @if ($u->is_active)
                                                    {{-- x-on:click + Js::from, not @click + @js(): Blade never compiles a
                                                         directive inside a component attribute, so @js() would be
                                                         emitted as literal text and the handler would be a syntax error. --}}
                                                    <x-ui.button
                                                        type="button"
                                                        variant="dangerSoft"
                                                        size="sm"
                                                        x-on:click="$dispatch('confirm-action', {
                                                            url: {{ Illuminate\Support\Js::from(route('admin.users.toggle-status', $u)) }},
                                                            method: 'PATCH',
                                                            title: 'Nonaktifkan akun ini?',
                                                            body: 'Pengguna tidak dapat login sampai akun diaktifkan kembali.',
                                                            confirm: 'Ya, nonaktifkan',
                                                            tone: 'danger',
                                                            trigger: $el,
                                                        })"
                                                    >Nonaktifkan</x-ui.button>
                                                @else
                                                    <form action="{{ route('admin.users.toggle-status', $u) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <x-ui.button type="submit" variant="secondary" size="sm">Aktifkan</x-ui.button>
                                                    </form>
                                                @endif
                                                <span class="w-px h-5 bg-slate-200" aria-hidden="true"></span>
                                            @endif

                                            <a
                                                href="{{ route('admin.users.edit', $u) }}"
                                                class="inline-flex items-center min-h-9 px-2.5 py-1.5 rounded-lg bg-primary-50 hover:bg-primary-100 text-primary-700 font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
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
            </div>

            {{-- Cards: mobile only --}}
            <ul class="md:hidden divide-y divide-slate-100">
                @foreach ($users as $u)
                    @php
                        $role = $u->primary_role_code;
                        $roleLabel = $role?->label() ?? 'Staf';
                    @endphp
                    <li class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 text-sm break-words">{{ $u->name }}</p>
                                <p class="text-xs text-slate-500 break-words">{{ $u->email }}</p>
                            </div>
                            @if ($u->is_active)
                                <x-ui.badge tone="success" :dot="true">Aktif</x-ui.badge>
                            @else
                                <x-ui.badge tone="danger" :dot="true">Nonaktif</x-ui.badge>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge :tone="$role === \App\Domain\Access\Enums\RoleCode::ADMIN ? 'primary' : 'neutral'" :title="$role?->value">{{ $roleLabel }}</x-ui.badge>
                            @if ($u->id === auth()->id())
                                <x-ui.badge tone="primary">Anda</x-ui.badge>
                            @endif
                        </div>

                        <div class="space-y-2 pt-1">
                            @if ($u->id !== auth()->id())
                                @if ($u->is_active)
                                    <x-ui.button
                                        type="button"
                                        variant="dangerSoft"
                                        size="sm"
                                        block
                                        x-on:click="$dispatch('confirm-action', {
                                            url: {{ Illuminate\Support\Js::from(route('admin.users.toggle-status', $u)) }},
                                            method: 'PATCH',
                                            title: 'Nonaktifkan akun ini?',
                                            body: 'Pengguna tidak dapat login sampai akun diaktifkan kembali.',
                                            confirm: 'Ya, nonaktifkan',
                                            tone: 'danger',
                                            trigger: $el,
                                        })"
                                    >Nonaktifkan</x-ui.button>
                                @else
                                    <form action="{{ route('admin.users.toggle-status', $u) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" variant="secondary" size="sm" block>Aktifkan</x-ui.button>
                                    </form>
                                @endif
                            @endif

                            <x-ui.button :href="route('admin.users.edit', $u)" variant="secondary" size="sm" block>Edit</x-ui.button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="p-4 border-t border-slate-200">
                {{ $users->links() }}
            </div>
        @endif
    </x-ui.card>
</div>

<x-ui.confirm-modal />
@endsection
