@props(['case'])

{{--
    Interactive before/after reveal. Drag the handle (pointer) or use the
    range slider (keyboard/AT) to wipe between the two photos. Shows real
    photos only when the case has them — otherwise an honest, on-brand
    "photos pending" panel, never a stand-in image (see before-after-card
    and docs/03-design-system.md §5 on imagery honesty).

    A11y: the range input is the real control (focusable, arrow-key driven,
    labelled); the visual handle mirrors its value. Motion is a CSS width
    transition only, disabled under prefers-reduced-motion via .cx-bar-grow
    being absent here (drag must be instant) — the wipe itself is not an
    animation, so it's safe.
--}}
@php
    $treatmentName = $case->treatment?->getTranslation('name', app()->getLocale());
    $clinicName = $case->clinic?->getTranslation('name', app()->getLocale());
@endphp

<figure class="cx-lift group overflow-hidden rounded-xl border border-ink-200 bg-white shadow-card hover:shadow-raised">
    @if ($case->hasPhotos())
        <div x-data="{ pos: 50, drag(e){ const r=$refs.frame.getBoundingClientRect(); const x=(e.touches?e.touches[0].clientX:e.clientX)-r.left; this.pos=Math.max(0,Math.min(100,(x/r.width)*100)); } }"
             x-ref="frame"
             class="relative aspect-[4/3] w-full cursor-ew-resize select-none overflow-hidden"
             @pointerdown="drag($event)" @pointermove="$event.buttons===1 && drag($event)"
             @touchstart.passive="drag($event)" @touchmove.passive="drag($event)">

            {{-- After (base layer, full frame) --}}
            <img src="{{ $case->after_url }}" alt="{{ __('After') }} — {{ $treatmentName }}"
                 class="absolute inset-0 h-full w-full object-cover" draggable="false" loading="lazy">
            <span class="absolute right-3 top-3 rounded-full bg-teal-500/85 px-2.5 py-1 font-mono text-[0.6rem] font-semibold uppercase tracking-wider text-white backdrop-blur">{{ __('After') }}</span>

            {{-- Before (same full frame, revealed by a clip-path wipe — no squish) --}}
            <img src="{{ $case->before_url }}" alt="{{ __('Before') }} — {{ $treatmentName }}"
                 class="absolute inset-0 h-full w-full object-cover" draggable="false" loading="lazy"
                 x-bind:style="`clip-path: inset(0 ${100 - pos}% 0 0)`">
            <span class="absolute left-3 top-3 rounded-full bg-brand-950/70 px-2.5 py-1 font-mono text-[0.6rem] font-semibold uppercase tracking-wider text-ink-50 backdrop-blur"
                  x-show="pos > 12">{{ __('Before') }}</span>

            {{-- Handle --}}
            <div class="pointer-events-none absolute inset-y-0" x-bind:style="`left: ${pos}%`">
                <div class="absolute inset-y-0 -ml-px w-0.5 bg-white/90 shadow-[0_0_12px_rgba(0,0,0,0.4)]"></div>
                <div class="absolute top-1/2 flex h-9 w-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border border-white/70 bg-white/95 text-brand-700 shadow-raised">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7l-4 5 4 5M16 7l4 5-4 5"/></svg>
                </div>
            </div>

            {{-- Real control for keyboard / assistive tech --}}
            <input type="range" min="0" max="100" x-model.number="pos"
                   aria-label="{{ __('Reveal before and after') }}"
                   class="absolute inset-x-0 bottom-0 z-10 h-10 w-full cursor-ew-resize opacity-0">
        </div>
    @else
        <div class="relative flex aspect-[4/3] w-full items-center justify-center overflow-hidden bg-gradient-to-br from-brand-900 to-brand-950">
            <span class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image: radial-gradient(circle, rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 18px 18px;"></span>
            <span class="font-mono text-xs uppercase tracking-widest text-ink-300">{{ __('Photos pending') }}</span>
        </div>
    @endif

    <figcaption class="flex items-center justify-between gap-3 p-4">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-ink-900">{{ $treatmentName }}</p>
            <p class="truncate text-xs text-ink-500">{{ $clinicName }}</p>
        </div>
        @if ($case->consent_confirmed)
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-[0.62rem] font-semibold text-teal-600">
                ✓ {{ __('Consent verified') }}
            </span>
        @endif
    </figcaption>
</figure>
