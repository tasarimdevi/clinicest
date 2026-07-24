@props(['review'])

<div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-ink-900">{{ $review->reviewer_name }}</p>
            <p class="text-xs text-ink-500">
                {{ $review->reviewerCountry?->name }}
                @if ($review->treatment)
                    &middot; {{ $review->treatment->getTranslation('name', app()->getLocale()) }}
                @endif
            </p>
        </div>
        <span class="shrink-0 font-mono text-sm font-semibold tabular-nums text-ink-700">★ {{ $review->rating }}</span>
    </div>

    @if ($review->is_verified)
        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-[0.68rem] font-semibold text-teal-600">
            ✓ {{ __('Verified treatment') }}
        </span>
    @endif

    @if ($review->title)
        <p class="mt-3 text-sm font-semibold text-ink-900">{{ $review->title }}</p>
    @endif
    <p class="mt-1 text-sm text-ink-600">{{ $review->body }}</p>
</div>
