<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('nav.guide'), 'url' => route('guide.index')],
            ['name' => $post->getTranslation('title', app()->getLocale())],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">
            {{ $post->getTranslation('title', app()->getLocale()) }}
        </h1>
        @if ($excerpt = $post->getTranslation('excerpt', app()->getLocale()))
            <p class="mt-4 max-w-2xl text-lg text-ink-600">{{ $excerpt }}</p>
        @endif
    </div>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <x-article-body :post="$post" />
    </div>

    <div class="border-t border-ink-200 bg-white py-14">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @if ($pillar)
                <a href="{{ route('guide.index') }}" class="text-sm font-medium text-brand-700 hover:underline">
                    &larr; {{ __('Back to') }} {{ $pillar->getTranslation('title', app()->getLocale()) }}
                </a>
            @endif

            @if ($siblings->isNotEmpty())
                <h2 class="mt-8 font-serif text-xl font-medium text-ink-900">{{ __('More in this guide') }}</h2>
                <ul class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($siblings as $sibling)
                        <li>
                            <a href="{{ route('guide.show', $sibling->slug) }}" class="block rounded-md border border-ink-200 bg-white p-4 hover:border-brand-300">
                                <p class="font-medium text-ink-900">{{ $sibling->getTranslation('title', app()->getLocale()) }}</p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

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
