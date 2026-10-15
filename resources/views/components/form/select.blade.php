@props([
    'name',
    'required' => false,
    'label' => null,
    'hint' => null,
    'placeholder' => null,
    'options' => [],
    'selected' => null,
    'empty' => null,
])

@php
    $id = $attributes->get('id') ?? $name;
    $hasError = $errors->has($name);
    $describedBy = trim(implode(' ', array_filter([
        $hint ? "$id-hint" : null,
        $hasError ? "$id-error" : null,
    ])));
    $current = (string) old($name, $selected);
@endphp

<div {{ $attributes->only('class') }}>
    @if ($label)
        <x-form.label :for="$id" :required="$required">{{ $label }}</x-form.label>
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="text-xs text-slate-500 mb-1.5">{{ $hint }}</p>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $id }}"
        @if ($required) required aria-required="true" @endif
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        class="w-full min-h-11 px-3.5 py-2.5 rounded-lg border bg-white text-sm text-slate-900
            focus:outline-hidden focus:ring-2 focus:border-primary-500 focus:ring-primary-500
            disabled:bg-slate-50 disabled:text-slate-500 disabled:cursor-not-allowed
            @if ($hasError) border-danger-400 focus:border-danger-500 focus:ring-danger-500 @else border-slate-300 @endif"
        {{ $attributes->except(['class', 'id']) }}
    >
        @if ($empty)
            <option value="" @if ($current === '') selected @endif>{{ $empty }}</option>
        @endif

        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @if ((string) $value === $current) selected @endif>{{ $text }}</option>
        @endforeach

        {{ $slot }}
    </select>

    @if ($hasError)
        <p id="{{ $id }}-error" role="alert" class="mt-1 text-xs text-danger-600 flex items-center gap-1">
            <svg aria-hidden="true" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $errors->first($name) }}
        </p>
    @endif
</div>
