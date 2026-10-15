@props([
    'title',
    'subtitle' => null,
])

{{--
    The page's own identity. No eyebrow or kicker above the heading — the
    heading carries its own weight.
--}}
<header {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4']) }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2 shrink-0">{{ $actions }}</div>
    @endisset
</header>
