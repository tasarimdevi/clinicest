<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('nav.guide')],
        ]" />

        @if ($pillar)
            <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">
                {{ $pillar->getTranslation('title', app()->getLocale()) }}
            </h1>
            @if ($excerpt = $pillar->getTranslation('excerpt', app()->getLocale()))
                <p class="mt-4 max-w-2xl text-lg text-ink-600">{{ $excerpt }}</p>
            @endif
        @else
            <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('nav.guide') }}</h1>
            <p class="mt-4 max-w-2xl text-lg text-ink-600">{{ __('Our full guide is being written — check back soon.') }}</p>
        @endif
    </div>

    @if ($pillar)
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <x-article-body :post="$pillar" />
        </div>
    @endif

    @if ($clusters->isNotEmpty())
        <div class="border-t border-ink-200 bg-white py-14">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('In this guide') }}</h2>
                @foreach ($clusters as $categoryName => $posts)
                    <div class="mt-6">
                        <h3 class="font-mono text-xs font-semibold uppercase tracking-wide text-gold-600">{{ $categoryName }}</h3>
                        <ul class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @foreach ($posts as $post)
                                <li>
                                    <a href="{{ route('guide.show', $post->slug) }}" class="block rounded-md border border-ink-200 bg-white p-4 hover:border-brand-300">
                                        <p class="font-medium text-ink-900">{{ $post->getTranslation('title', app()->getLocale()) }}</p>
                                        @if ($excerpt = $post->getTranslation('excerpt', app()->getLocale()))
                                            <p class="mt-1 text-sm text-ink-500">{{ \Illuminate\Support\Str::limit($excerpt, 90) }}</p>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-brand-950 px-8 py-12 text-center text-ink-50">
            <h2 class="font-serif text-2xl font-medium">{{ __('home.final_cta_title') }}</h2>
            <p class="mt-2 text-ink-300">{{ __('home.final_cta_subtitle') }}</p>
            <x-button :href="route('get-quote')" as="a" variant="gold" size="lg" class="mt-6">
                {{ __('home.hero_cta') }}
            </x-button>
        </div>
    </div>
</div>
