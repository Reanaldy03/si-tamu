@props([
    'name',
    'type' => 'text',
    'value' => null,
    'required' => false,
    'label' => null,
    'hint' => null,
    'placeholder' => null,
    'autocomplete' => null,
    'inputmode' => null,
    'maxlength' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'leading' => false,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = $errors->has($name);
    $describedBy = trim(implode(' ', array_filter([
        $hint ? "$id-hint" : null,
        $hasError ? "$id-error" : null,
    ])));
    $resolved = old($name, $value);
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <x-form.label :for="$id" :required="$required">{{ $label }}</x-form.label>
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-xs text-slate-500 mb-1.5">{{ $hint }}</p>
    @endif

    <div class="relative">
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            @if ($resolved !== null && $resolved !== '') value="{{ $resolved }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($inputmode) inputmode="{{ $inputmode }}" @endif
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
            @if ($min !== null) min="{{ $min }}" @endif
            @if ($max !== null) max="{{ $max }}" @endif
            @if ($step !== null) step="{{ $step }}" @endif
            @if ($required) required aria-required="true" @endif
            @if ($hasError) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            class="w-full min-h-11 px-3.5 py-2.5 rounded-lg border bg-white text-sm text-slate-900
                placeholder:text-slate-500
                focus:outline-hidden focus:ring-2 focus:border-primary-500 focus:ring-primary-500
                disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed
                @if ($leading) pl-10 @endif
                @if ($hasError) border-danger-400 focus:border-danger-500 focus:ring-danger-500 @else border-slate-300 @endif"
            {{ $attributes->except(['class', 'id']) }}
        >

        @if (isset($leadingSlot))
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                {{ $leadingSlot }}
            </div>
        @endif
    </div>

    @if ($hasError)
        <p id="{{ $id }}-error" role="alert" class="mt-1 text-xs text-danger-600 flex items-center gap-1">
            <svg aria-hidden="true" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $errors->first($name) }}
        </p>
    @endif
</div>
