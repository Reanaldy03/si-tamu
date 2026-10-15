@props([
    'id' => 'confirm-modal',
    'defaultTone' => 'danger',
])

@php
    $titleId = $id . '-title';
    $bodyId = $id . '-body';
@endphp

{{--
    One modal per page, driven by events. Triggers:

        <x-ui.button
            type="button"
            x-on:click="$dispatch('confirm-action', {
                url: {{ Illuminate\Support\Js::from(route('admin.users.toggle-status', $user)) }},
                method: 'PATCH',
                title: 'Nonaktifkan akun ini?',
                body: 'Pengguna tidak dapat login sampai akun diaktifkan kembali.',
                confirm: 'Ya, nonaktifkan',
                tone: 'danger',
                trigger: $el,
            })"
        >Nonaktifkan</x-ui.button>

    Two rules, both learned the hard way:

    1. Use `{{ Illuminate\Support\Js::from(...) }}` for the payload. Blade never
       compiles a directive (`@js`, `@json`, …) inside a component attribute
       value, so `@js(...)` there is emitted as literal text and the handler
       becomes a JS syntax error. An escaped echo works fine.
    2. Prefer `x-on:click` over `@click` inside a component tag; it is a plain
       HTML attribute name and cannot be mistaken for a directive.

    Focus moves into the dialog, is trapped, Escape closes it, and focus
    returns to the trigger that opened it (DESIGN.md §44.2).
--}}
<div
    x-data="{
        open: false,
        url: '',
        method: 'POST',
        title: '',
        body: '',
        confirmLabel: 'Ya, lanjutkan',
        tone: @js($defaultTone),
        withReason: false,
        reasonRequired: false,
        trigger: null,
        show(detail) {
            this.url = detail.url ?? '';
            this.method = detail.method ?? 'POST';
            this.title = detail.title ?? 'Konfirmasi tindakan';
            this.body = detail.body ?? '';
            this.confirmLabel = detail.confirm ?? 'Ya, lanjutkan';
            this.tone = detail.tone ?? @js($defaultTone);
            this.withReason = !! detail.withReason;
            this.reasonRequired = !! detail.reasonRequired;
            this.trigger = detail.trigger ?? document.activeElement;
            this.open = true;
            this.$nextTick(() => {
                if (this.withReason) { this.$refs.reason?.focus(); }
                else { this.$refs.confirmButton?.focus(); }
            });
        },
        close() {
            this.open = false;
            this.$nextTick(() => this.trigger?.focus?.());
        },
    }"
    @confirm-action.window="show($event.detail)"
    @keydown.escape.window="if (open) close()"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    x-cloak
>
    <template x-if="open">
        <div class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div
                class="absolute inset-0 bg-slate-900/60"
                x-transition:enter="transition-opacity ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click="close()"
                aria-hidden="true"
            ></div>

            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="{{ $titleId }}"
                aria-describedby="{{ $bodyId }}"
                x-trap.noscroll="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-xl border border-slate-200 overflow-hidden"
            >
                <form :action="url" method="POST" @submit="open = false" class="p-5 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="method">

                    <div class="flex items-start gap-3">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                            :class="tone === 'danger' ? 'bg-danger-50 text-danger-600' : 'bg-primary-50 text-primary-600'"
                        >
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <h2 id="{{ $titleId }}" class="text-base font-semibold text-slate-900" x-text="title"></h2>
                            <p id="{{ $bodyId }}" class="mt-1 text-sm text-slate-600" x-text="body"></p>
                        </div>
                    </div>

                    <div x-show="withReason" x-cloak class="space-y-1.5">
                        <label for="{{ $id }}-reason" class="block text-sm font-medium text-slate-700">
                            Alasan
                            <span class="text-danger-600" aria-hidden="true">*</span>
                        </label>
                        <textarea
                            id="{{ $id }}-reason"
                            name="reason"
                            x-ref="reason"
                            rows="3"
                            :required="reasonRequired"
                            aria-required="true"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm text-slate-900 placeholder:text-slate-500 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            placeholder="Tuliskan alasan perubahan agar tercatat di audit log…"
                        ></textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-1">
                        <button
                            type="button"
                            @click="close()"
                            class="inline-flex items-center justify-center min-h-11 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-primary-500"
                        >Batal</button>

                        <button
                            type="submit"
                            x-ref="confirmButton"
                            class="inline-flex items-center justify-center min-h-11 px-4 py-2.5 rounded-lg text-white text-sm font-semibold shadow-xs transition focus:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2"
                            :class="tone === 'danger' ? 'bg-danger-600 hover:bg-danger-700 focus-visible:ring-danger-500' : 'bg-primary-600 hover:bg-primary-700 focus-visible:ring-primary-500'"
                            x-text="confirmLabel"
                        ></button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>
