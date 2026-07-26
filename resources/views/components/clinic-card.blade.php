@props(['clinic'])

@php
    $cover = $clinic->media->firstWhere('is_cover', true) ?? $clinic->media->first();
    // Branded, honest fallback for a clinic with no photo yet: a monogram
    // of its initials on the brand gradient — reads as intentional, not
    // broken, and never fabricates a stock clinic image.
    $initials = \Illuminate\Support\Str::of($clinic->getTranslation('name', app()->getLocale()))
        ->explode(' ')->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->take(2)->implode('');
@endphp

<a href="{{ route('clinics.show', $clinic->slug) }}"
   class="group flex flex-col overflow-hidden rounded-lg border border-ink-200 bg-white shadow-card transition duration-200 hover:-translate-y-1 hover:shadow-raised">
    <div class="relative aspect-[4/3] w-full overflow-hidden">
        @if ($cover)
            <img src="{{ $cover->url }}" alt="{{ $clinic->getTranslation('name', app()->getLocale()) }}"
                 class="h-full w-full object-cover transition duration-300 group-hover:scale-105" loading="lazy" width="400" height="300">
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-900 to-brand-950">
                <span class="font-serif text-4xl font-medium tracking-wide text-white/85">{{ $initials ?: '—' }}</span>
                <span class="pointer-events-none absolute inset-0 opacity-[0.12]"
                      style="background-image: radial-gradient(circle, rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 18px 18px;"></span>
            </div>
        @endif
        <div class="absolute right-2 top-2">
            <x-verification-badge :tier="$clinic->verification_tier->value" />
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-1.5 p-4">
        <h3 class="font-serif text-base font-medium text-ink-900 group-hover:text-brand-600">
            {{ $clinic->getTranslation('name', app()->getLocale()) }}
        </h3>
        <p class="text-xs text-ink-500">{{ $clinic->city?->name }}</p>

        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
            @if ($clinic->rating_count > 0)
                <span class="font-mono font-medium tabular-nums text-gold-600">★ {{ number_format($clinic->rating_avg, 1) }}
                    <span class="text-ink-400">({{ $clinic->rating_count }})</span></span>
            @endif
            @if ($clinic->response_time_minutes)
                <span class="inline-flex items-center gap-1 text-ink-500">
                    <svg class="h-3.5 w-3.5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ __('Responds within') }} {{ $clinic->response_time_minutes }} {{ __('min') }}
                </span>
            @endif
        </div>

        @if (! empty($clinic->languages_json))
            <p class="mt-0.5 font-mono text-[0.68rem] uppercase tracking-wide text-ink-400">
                {{ collect($clinic->languages_json)->map(fn ($l) => strtoupper($l))->implode(' · ') }}
            </p>
        @endif
    </div>
</a>
