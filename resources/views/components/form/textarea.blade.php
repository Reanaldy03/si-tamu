@props([
    'name',
    'required' => false,
    'label' => null,
    'hint' => null,
    'placeholder' => null,
    'rows' => 3,
    'maxlength' => null,
    'showCount' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = $errors->has($name);
    $describedBy = trim(implode(' ', array_filter([
        $hint ? "$id-hint" : null,
        $hasError ? "$id-error" : null,
        $showCount && $maxlength ? "$id-count" : null,
    ])));
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <x-form.label :for="$id" :required="$required">{{ $label }}</x-form.label>
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-xs text-slate-500 mb-1.5">{{ $hint }}</p>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        @if ($required) required aria-required="true" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        @if ($showCount && $maxlength) x-data="{ n: $el.value.length }" @input="n = $el.value.length" @endif
        class="w-full px-3.5 py-2.5 rounded-lg border bg-white text-sm text-slate-900
            placeholder:text-slate-500
            focus:outline-hidden focus:ring-2 focus:border-primary-500 focus:ring-primary-500
            disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed
            @if ($hasError) border-danger-400 focus:border-danger-500 focus:ring-danger-500 @else border-slate-300 @endif"
        {{ $attributes->except(['class', 'id']) }}
    >{{ old($name, $slot) }}</textarea>

    <div class="flex items-start justify-between gap-3 mt-1">
        @if ($hasError)
            <p id="{{ $id }}-error" role="alert" class="text-xs text-danger-600 flex items-center gap-1">
                <svg aria-hidden="true" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ $errors->first($name) }}
            </p>
        @else
            <span></span>
        @endif

        @if ($showCount && $maxlength)
            <p id="{{ $id }}-count" class="text-xs text-slate-500 tabular ml-auto" aria-live="off">
                <span x-text="n">{{ strlen(old($name, $slot)) }}</span>/{{ $maxlength }}
            </p>
        @endif
    </div>
</div>
