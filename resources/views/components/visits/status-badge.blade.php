@props([
    'status',
    'dot' => true,
])

@php
    use App\Domain\Visits\Enums\VisitStatus;

    $resolved = $status instanceof VisitStatus
        ? $status
        : VisitStatus::from((string) $status);
@endphp

{{--
    DESIGN.md §21.4 — colour is never the only signal. The dot and the written
    label always ship together, and there is deliberately no pulse: a pulse
    reads as "in progress", which is wrong for terminal states.
--}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2 py-0.5 rounded border text-xs font-semibold whitespace-nowrap ' . $resolved->badgeClasses()]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $resolved->dotClasses() }}" aria-hidden="true"></span>
    @endif
    {{ $resolved->label() }}
</span>
