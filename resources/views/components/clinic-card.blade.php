@props(['clinic'])

<a href="{{ route('clinics.show', $clinic->slug) }}"
   class="group flex flex-col overflow-hidden rounded-lg border border-ink-200 bg-white shadow-card transition hover:-translate-y-0.5 hover:shadow-raised">
    <div class="aspect-[4/3] w-full bg-ink-100">
        @if ($clinic->media->first())
            <img src="{{ $clinic->media->first()->path }}" alt="{{ $clinic->getTranslation('name', app()->getLocale()) }}"
                 class="h-full w-full object-cover" loading="lazy" width="400" height="300">
        @endif
    </div>
    <div class="flex flex-1 flex-col gap-2 p-4">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-serif text-base font-medium text-ink-900 group-hover:text-brand-600">
                {{ $clinic->getTranslation('name', app()->getLocale()) }}
            </h3>
            <x-verification-badge :tier="$clinic->verification_tier->value" />
        </div>
        <p class="text-xs text-ink-500">{{ $clinic->city?->name }}</p>
        @if ($clinic->rating_count > 0)
            <p class="font-mono text-xs font-medium tabular-nums text-ink-700">
                ★ {{ number_format($clinic->rating_avg, 1) }} ({{ $clinic->rating_count }})
            </p>
        @endif
    </div>
</a>
