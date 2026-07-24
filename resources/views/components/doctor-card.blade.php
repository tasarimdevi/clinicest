@props(['doctor'])

<a href="{{ route('doctors.show', $doctor->slug) }}"
   class="group flex flex-col gap-1.5 rounded-lg border border-ink-200 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-raised">
    <h3 class="font-serif text-base font-medium text-ink-900 group-hover:text-brand-600">
        {{ $doctor->full_name }}
    </h3>
    @if ($doctor->getTranslation('specialty', app()->getLocale()))
        <p class="text-sm text-ink-600">{{ $doctor->getTranslation('specialty', app()->getLocale()) }}</p>
    @endif
    <p class="text-xs text-ink-500">{{ $doctor->clinic?->getTranslation('name', app()->getLocale()) }}</p>
    <div class="mt-2 flex items-center justify-between text-xs">
        <span class="font-mono tabular-nums text-ink-500">
            @if ($doctor->years_experience)
                {{ $doctor->years_experience }} {{ __('yrs experience') }}
            @endif
        </span>
        @if ($doctor->rating_count > 0)
            <span class="font-mono font-medium tabular-nums text-ink-700">
                ★ {{ number_format($doctor->rating_avg, 1) }}
            </span>
        @endif
    </div>
</a>
