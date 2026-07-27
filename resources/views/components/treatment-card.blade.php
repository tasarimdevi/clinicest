@props(['treatment', 'code' => null])

{{--
    Photo-forward "manifest gate" card — keeps the departure-board identity
    (the GATE code) as a small badge over a real photo instead of the
    former text-only layout (see docs/03-design-system.md §1). $code is an
    optional boarding-gate-style label; falls back to one derived from the
    treatment's own slug so every card gets one for free.

    Photos are static marketing images (public/images/treatments/{slug}.webp)
    — Treatment has no per-record upload (unlike Clinic/Doctor), and these
    are generic category photography, not a claim about a specific clinic,
    so this doesn't collide with the "never stock for a specific claim"
    rule reserved for clinic/doctor/before-after imagery. No file yet ->
    the same branded-gradient monogram fallback used by x-clinic-card.

    Unlike the old version, this card supplies its own border/shadow/radius,
    so callers must lay it out in a spaced grid (gap-6), not the old
    hairline-divider "gap-px + bg-ink-200" manifest grid.
--}}
@php
    $gateCode = $code ?? 'GATE '.strtoupper(substr(preg_replace('/[^a-z]/', '', $treatment->slug), 0, 2)).'-'.str_pad((string) $treatment->id, 2, '0', STR_PAD_LEFT);
    $name = $treatment->getTranslation('name', app()->getLocale());
    $initials = \Illuminate\Support\Str::of($name)->explode(' ')->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->take(2)->implode('');

    $photo = null;
    foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
        $relative = "images/treatments/{$treatment->slug}.{$ext}";
        if (file_exists(public_path($relative))) {
            $photo = asset($relative);
            break;
        }
    }
@endphp

<a href="{{ route('treatments.show', $treatment->slug) }}"
   class="group flex flex-col overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card transition duration-200 hover:-translate-y-1 hover:shadow-raised">
    <div class="relative aspect-[4/3] w-full overflow-hidden">
        @if ($photo)
            <img src="{{ $photo }}" alt="" class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/10 to-transparent"></div>
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-900 to-brand-950">
                <span class="font-serif text-4xl font-medium tracking-wide text-white/85">{{ $initials ?: '—' }}</span>
                <span class="pointer-events-none absolute inset-0 opacity-[0.12]"
                      style="background-image: radial-gradient(circle, rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 18px 18px;"></span>
            </div>
        @endif
        <span class="absolute left-3 top-3 rounded-full bg-brand-950/70 px-2.5 py-1 font-mono text-[0.62rem] font-semibold uppercase tracking-wider text-ink-50 backdrop-blur">
            {{ $gateCode }}
        </span>
    </div>

    <div class="flex flex-1 flex-col gap-2 p-5">
        <h3 class="text-base font-semibold text-ink-900 group-hover:text-brand-600">
            {{ $name }}
        </h3>

        <div class="mt-auto flex items-center justify-between gap-3 pt-1">
            @if ($treatment->base_price_min)
                <p class="font-mono text-sm font-semibold text-teal-600 tabular-nums">
                    {{ __('from') }} {{ $treatment->currency }} {{ number_format($treatment->base_price_min / 100, 0) }}
                </p>
            @endif
            @if ($treatment->avg_duration_min || $treatment->recovery_days)
                <div class="flex shrink-0 items-center gap-2.5 font-mono text-xs text-ink-400">
                    @if ($treatment->avg_duration_min)
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $treatment->avg_duration_min }}{{ __('min') }}
                        </span>
                    @endif
                    @if ($treatment->recovery_days)
                        <span class="inline-flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            {{ $treatment->recovery_days }}{{ __('days') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</a>
