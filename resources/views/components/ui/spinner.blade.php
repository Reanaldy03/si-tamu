@props([
    'size' => 'md',
    'label' => null,
])

@php
    $dimension = match ($size) {
        'sm' => 'w-4 h-4',
        'lg' => 'w-6 h-6',
        default => 'w-5 h-5',
    };
@endphp

<svg
    {{ $attributes->merge(['class' => "$dimension animate-spin shrink-0"]) }}
    viewBox="0 0 24 24"
    fill="none"
    aria-hidden="true"
>
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
</svg>

@if ($label)
    <span class="sr-only">{{ $label }}</span>
@endif
