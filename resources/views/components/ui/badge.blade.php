@props([
    'tone' => 'neutral',
    'dot' => false,
])

@php
    $tones = [
        'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
        'primary' => 'bg-primary-50 text-primary-700 border-primary-200',
        'success' => 'bg-success-50 text-success-800 border-success-200',
        'warning' => 'bg-warning-50 text-warning-800 border-warning-200',
        'danger' => 'bg-danger-50 text-danger-800 border-danger-200',
        'info' => 'bg-info-50 text-info-800 border-info-200',
    ];

    $dots = [
        'neutral' => 'bg-slate-400',
        'primary' => 'bg-primary-600',
        'success' => 'bg-success-600',
        'warning' => 'bg-warning-500',
        'danger' => 'bg-danger-600',
        'info' => 'bg-info-600',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded border text-xs font-semibold whitespace-nowrap ' . ($tones[$tone] ?? $tones['neutral'])]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $dots[$tone] ?? $dots['neutral'] }}" aria-hidden="true"></span>
    @endif
    {{ $slot }}
</span>
