@props(['case'])

{{--
    Shows real before/after photos only when both exist. A case without
    photos yet renders an honest "pending" placeholder rather than a
    stand-in image — see docs/04-wireframes.md §10 and the
    before_after_cases migration for why.
--}}
<div class="overflow-hidden rounded-lg border border-ink-200 bg-white shadow-card">
    <div class="grid grid-cols-2">
        @if ($case->hasPhotos())
            <img src="{{ $case->before_url }}" alt="{{ __('Before') }}" class="aspect-square w-full object-cover" loading="lazy">
            <img src="{{ $case->after_url }}" alt="{{ __('After') }}" class="aspect-square w-full object-cover" loading="lazy">
        @else
            <div class="col-span-2 flex aspect-[2/1] items-center justify-center bg-ink-50 text-sm text-ink-400">
                {{ __('Photos pending') }}
            </div>
        @endif
    </div>
    <div class="p-4">
        <h3 class="text-sm font-semibold text-ink-900">{{ $case->getTranslation('title', app()->getLocale()) }}</h3>
        <p class="mt-1 text-xs text-ink-500">
            <a href="{{ route('treatments.show', $case->treatment->slug) }}" class="hover:text-brand-600">
                {{ $case->treatment->getTranslation('name', app()->getLocale()) }}
            </a>
            &middot;
            <a href="{{ route('clinics.show', $case->clinic->slug) }}" class="hover:text-brand-600">
                {{ $case->clinic->getTranslation('name', app()->getLocale()) }}
            </a>
        </p>
        @if ($case->consent_confirmed)
            <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-[0.68rem] font-semibold text-teal-600">
                ✓ {{ __('Consent verified') }}
            </span>
        @endif
    </div>
</div>
