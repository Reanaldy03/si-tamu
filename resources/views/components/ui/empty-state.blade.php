@props([
    'title',
    'description' => null,
    'kind' => 'empty',
    'headingLevel' => 2,
])

@php
    // DESIGN.md §36 — the copy must name the actual cause, never a global
    // "no data" when the truth is "no match" or "not yours to see".
    $icon = match ($kind) {
        'filtered' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        'restricted' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        default => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
    };

    $heading = 'h' . max(2, min(6, (int) $headingLevel));
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center px-6 py-12 rounded-2xl border border-dashed border-slate-300 bg-slate-50/50']) }}>
    <div class="w-12 h-12 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 mb-4 shadow-xs">
        <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}" />
        </svg>
    </div>

    <{{ $heading }} class="text-base font-semibold text-slate-800">{{ $title }}</{{ $heading }}>

    @if ($description)
        <p class="mt-1.5 text-sm text-slate-500 max-w-md">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $action }}</div>
    @endisset
</div>
