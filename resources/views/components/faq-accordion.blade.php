@props(['items' => []])

{{--
    Accessible animated accordion. `items` is an array of ['q' => ..., 'a' => ...].
    One panel open at a time; the header is a real <button> with aria-expanded
    and aria-controls, and panels use a grid-rows 0fr→1fr transition (smooth,
    no fixed max-height guesswork). Reduced motion is honoured because the
    transition is on grid-template-rows, which the reduced-motion block below
    the app.css utilities does not need to touch — but to be safe we keep the
    transition short and content readable at every frame.
--}}
<div x-data="{ open: 0 }" class="divide-y divide-ink-200 overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card">
    @foreach ($items as $i => $item)
        <div>
            <h3>
                <button type="button"
                        @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                        :aria-expanded="open === {{ $i }}"
                        aria-controls="faq-panel-{{ $i }}"
                        class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left transition hover:bg-ink-50">
                    <span class="text-base font-semibold text-ink-900">{{ $item['q'] }}</span>
                    <svg class="h-5 w-5 shrink-0 text-brand-600 transition-transform duration-300"
                         :class="open === {{ $i }} ? 'rotate-45' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </h3>
            <div id="faq-panel-{{ $i }}"
                 class="grid transition-all duration-300 ease-out"
                 :class="open === {{ $i }} ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
                <div class="overflow-hidden">
                    <p class="px-6 pb-6 text-sm leading-relaxed text-ink-600">{{ $item['a'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>
