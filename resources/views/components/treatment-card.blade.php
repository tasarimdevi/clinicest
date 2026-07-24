@props(['treatment'])

<a href="{{ route('treatments.show', $treatment->slug) }}"
   class="group flex flex-col rounded-lg border border-ink-200 bg-white p-5 shadow-card transition hover:-translate-y-0.5 hover:shadow-raised">
    <div class="flex h-11 w-11 items-center justify-center rounded-md bg-brand-50 text-brand-700">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
        </svg>
    </div>
    <h3 class="mt-4 text-base font-semibold text-ink-900 group-hover:text-brand-700">
        {{ $treatment->getTranslation('name', app()->getLocale()) }}
    </h3>
    @if ($treatment->base_price_min)
        <p class="mt-1 text-sm text-ink-500">
            {{ __('from') }} {{ $treatment->currency }} {{ number_format($treatment->base_price_min / 100, 0) }}
        </p>
    @endif
</a>
