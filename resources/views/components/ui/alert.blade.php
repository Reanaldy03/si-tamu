@props([
    'tone' => 'info',
    'title' => null,
    'focusable' => false,
])

@php
    $tones = [
        'success' => ['bg-success-50 border-success-200 text-success-800', 'text-success-600',
            'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'danger' => ['bg-danger-50 border-danger-200 text-danger-800', 'text-danger-600',
            'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'warning' => ['bg-warning-50 border-warning-200 text-warning-800', 'text-warning-600',
            'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        'info' => ['bg-info-50 border-info-200 text-info-800', 'text-info-600',
            'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        'primary' => ['bg-primary-50 border-primary-200 text-primary-800', 'text-primary-600',
            'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];

    [$surface, $iconColor, $path] = $tones[$tone] ?? $tones['info'];
    $isDanger = $tone === 'danger';
@endphp

<div
    {{ $attributes->merge(['class' => "p-4 rounded-xl border text-sm flex items-start gap-2.5 shadow-xs $surface"]) }}
    @if ($focusable) tabindex="-1" @endif
    @if ($isDanger) role="alert" @else role="status" aria-live="polite" @endif
>
    <svg aria-hidden="true" class="w-5 h-5 shrink-0 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" />
    </svg>

    <div class="min-w-0 flex-1">
        @if ($title)
            <p class="font-semibold mb-0.5">{{ $title }}</p>
        @endif
        <div class="break-words">{{ $slot }}</div>
    </div>
</div>
