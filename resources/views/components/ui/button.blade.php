@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'loading' => null,
    'loadingLabel' => 'Memproses…',
    'block' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition '
        . 'focus:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2 '
        . 'disabled:opacity-60 disabled:cursor-not-allowed disabled:pointer-events-none';

    $sizes = [
        'sm' => 'min-h-9 px-3 py-2 text-xs',
        'md' => 'min-h-11 px-4 py-2.5 text-sm',
        'lg' => 'min-h-12 px-6 py-3 text-base',
    ];

    $variants = [
        'primary' => 'bg-primary-600 text-white shadow-xs hover:bg-primary-700 focus-visible:ring-primary-500',
        'secondary' => 'bg-white text-slate-700 border border-slate-300 shadow-xs hover:bg-slate-50 focus-visible:ring-primary-500',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-primary-500',
        'danger' => 'bg-danger-600 text-white shadow-xs hover:bg-danger-700 focus-visible:ring-danger-500',
        'dangerSoft' => 'bg-danger-50 text-danger-700 border border-danger-200 hover:bg-danger-100 focus-visible:ring-danger-500',
        'success' => 'bg-success-600 text-white shadow-xs hover:bg-success-700 focus-visible:ring-success-500',
    ];

    $classes = trim(implode(' ', [
        $base,
        $sizes[$size] ?? $sizes['md'],
        $variants[$variant] ?? $variants['primary'],
        $block ? 'w-full' : '',
    ]));

    /*
     * `loading` is an Alpine expression, passed as a plain attribute:
     *
     *     <x-ui.button type="submit" loading="submitting">Simpan</x-ui.button>
     *
     * Use the plain form, never `:loading="submitting"` — a bound attribute is
     * compiled as raw PHP, so it would resolve to the undefined constant
     * `submitting` and 500 the page. A bound boolean (`:loading="true"`) is
     * deliberately ignored rather than guessed at.
     */
    $loadingExpr = is_string($loading) && trim($loading) !== '' ? trim($loading) : null;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if ($loadingExpr) :disabled="{{ $loadingExpr }}" @endif
    >
        @if ($loadingExpr)
            {{--
                Both labels share one grid cell so the button keeps its width
                when it flips to the loading state (DESIGN.md §29.3).
            --}}
            <span class="grid place-items-center">
                <span class="col-start-1 row-start-1 inline-flex items-center gap-2" x-show="! ({{ $loadingExpr }})">{{ $slot }}</span>
                <span class="col-start-1 row-start-1 inline-flex items-center gap-2" x-show="{{ $loadingExpr }}" x-cloak>
                    <x-ui.spinner size="sm" />
                    <span>{{ $loadingLabel }}</span>
                </span>
            </span>
        @else
            {{ $slot }}
        @endif
    </button>
@endif
