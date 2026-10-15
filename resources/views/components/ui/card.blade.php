@props([
    'title' => null,
    'description' => null,
    'bodyClass' => 'p-5',
    'flush' => false,
])

@php
    $hasHeader = $title || isset($header);
@endphp

{{--
    One surface. Never place a card inside a card — use dividers and headings
    for sub-sections instead. Radii stay at 12-16px (rounded-xl / rounded-2xl).
--}}
<section {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden']) }}>
    @if ($hasHeader)
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/70 flex items-start justify-between gap-3">
            <div class="min-w-0">
                @if ($title)
                    <h2 class="text-base font-semibold text-slate-900 leading-tight">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $description }}</p>
                @endif
            </div>
            @isset($header)
                <div class="shrink-0">{{ $header }}</div>
            @endisset
        </div>
    @endif

    <div @class([$bodyClass => ! $flush])>
        {{ $slot }}
    </div>
</section>
