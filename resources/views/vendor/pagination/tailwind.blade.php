@php
    /*
     * Overrides Laravel's vendor pagination view, whose markup shipped
     * framework `gray-*` / `blue-*` hues and a `dark:` theme the rest of the
     * product does not have. Restyled onto the SI-TAMU palette so pagination
     * stops being the one surface that belongs to no design system.
     */
    $btn = 'inline-flex items-center justify-center min-h-11 min-w-11 px-3 py-2 rounded-lg border text-sm font-medium transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500';
    $idle = 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50';
    $active = 'bg-primary-600 border-primary-600 text-white shadow-xs';
    $disabled = 'bg-white border-slate-200 text-slate-400 cursor-not-allowed';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-xs text-slate-500 order-2 sm:order-1">
            Menampilkan
            <span class="font-semibold text-slate-700 tabular">{{ $paginator->firstItem() }}</span>
            &ndash;
            <span class="font-semibold text-slate-700 tabular">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-semibold text-slate-700 tabular">{{ $paginator->total() }}</span>
            data
        </p>

        <div class="flex items-center justify-between gap-1 order-1 sm:order-2 sm:justify-end">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="{{ $btn }} {{ $disabled }}" aria-disabled="true" aria-label="Halaman sebelumnya">
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="ml-1.5 hidden sm:inline">Sebelumnya</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $btn }} {{ $idle }}" aria-label="Halaman sebelumnya">
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="ml-1.5 hidden sm:inline">Sebelumnya</span>
                </a>
            @endif

            {{-- Page numbers: desktop only, the count above covers small screens --}}
            <div class="hidden sm:flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="{{ $btn }} {{ $disabled }}" aria-hidden="true">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="{{ $btn }} {{ $active }} tabular" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="{{ $btn }} {{ $idle }} tabular" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $btn }} {{ $idle }}" aria-label="Halaman berikutnya">
                    <span class="mr-1.5 hidden sm:inline">Berikutnya</span>
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            @else
                <span class="{{ $btn }} {{ $disabled }}" aria-disabled="true" aria-label="Halaman berikutnya">
                    <span class="mr-1.5 hidden sm:inline">Berikutnya</span>
                    <svg aria-hidden="true" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
