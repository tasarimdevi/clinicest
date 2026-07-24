<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('nav.reviews'), 'url' => route('reviews.index')],
        ['name' => $clinic->getTranslation('name', app()->getLocale())],
    ]" />

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-serif text-3xl font-medium text-ink-900">
                {{ __('Reviews for') }} {{ $clinic->getTranslation('name', app()->getLocale()) }}
            </h1>
            <a href="{{ route('clinics.show', $clinic->slug) }}" class="mt-1 inline-block text-sm text-brand-600 hover:underline">
                {{ __('View clinic profile') }}
            </a>
        </div>
        @if ($clinic->rating_count > 0)
            <div class="text-right">
                <p class="font-mono text-3xl font-semibold tabular-nums text-ink-900">★ {{ number_format($clinic->rating_avg, 1) }}</p>
                <p class="text-xs text-ink-500">{{ $clinic->rating_count }} {{ __('reviews') }}</p>
            </div>
        @endif
    </div>

    @if ($breakdown->isNotEmpty())
        <div class="mt-6 space-y-1.5">
            @for ($i = 5; $i >= 1; $i--)
                @php $count = $breakdown->get($i, 0); @endphp
                <div class="flex items-center gap-3 text-xs text-ink-500">
                    <span class="w-8 font-mono">★ {{ $i }}</span>
                    <div class="h-1.5 flex-1 rounded-full bg-ink-100">
                        <div class="h-1.5 rounded-full bg-gold-500" style="width: {{ $clinic->rating_count > 0 ? ($count / $clinic->rating_count) * 100 : 0 }}%"></div>
                    </div>
                    <span class="w-6 text-right font-mono tabular-nums">{{ $count }}</span>
                </div>
            @endfor
        </div>
    @endif

    <div class="mt-8 space-y-4">
        @forelse ($reviews as $review)
            <x-review-card :review="$review" />
        @empty
            <p class="text-sm text-ink-500">{{ __('No reviews yet.') }}</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $reviews->links() }}
    </div>
</div>
