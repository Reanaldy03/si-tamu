@props([
    'for' => null,
    'required' => false,
    'hint' => null,
])

{{--
    Sentence case, not ALL CAPS (DESIGN.md §22.3). Required fields are marked
    with a symbol and an accessible word, never colour alone (§9.5).
--}}
<label
    @if ($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'block text-sm font-medium text-slate-700 mb-1.5']) }}
>
    {{ $slot }}@if ($required)<span class="text-danger-600 ml-0.5" aria-hidden="true">*</span><span class="sr-only"> (wajib diisi)</span>@endif
</label>

@if ($hint)
    <p class="text-xs text-slate-500 mb-1.5">{{ $hint }}</p>
@endif
