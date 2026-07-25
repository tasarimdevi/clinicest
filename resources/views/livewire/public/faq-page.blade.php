<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('nav.faq')],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('Frequently asked questions') }}</h1>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row">
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('Search questions…') }}"
                class="w-full max-w-sm rounded-md border-ink-300 text-sm"
            >
            @if ($categories->isNotEmpty())
                <select wire:model.live="category" class="w-full max-w-xs rounded-md border-ink-300 text-sm">
                    <option value="">{{ __('All categories') }}</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        @forelse ($faqsByCategory as $categoryName => $faqs)
            <div class="mb-10">
                <h2 class="font-serif text-lg font-medium text-ink-900">{{ $categoryName }}</h2>
                <div class="mt-3">
                    @foreach ($faqs as $faq)
                        <details class="border-b border-ink-200 py-4">
                            <summary class="cursor-pointer font-medium text-ink-900">
                                {{ $faq->getTranslation('question', app()->getLocale()) }}
                            </summary>
                            <p class="mt-2 text-sm text-ink-600">{{ $faq->getTranslation('answer', app()->getLocale()) }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-sm text-ink-500">{{ __('No questions match your search.') }}</p>
        @endforelse
    </div>

    <div class="mx-auto max-w-4xl px-4 pb-14 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-brand-950 px-8 py-12 text-center text-ink-50">
            <h2 class="font-serif text-2xl font-medium">{{ __("Still have questions?") }}</h2>
            <p class="mt-2 text-ink-300">{{ __('Our team replies within 24 hours.') }}</p>
            <div class="mt-6 flex flex-wrap justify-center gap-4">
                <x-button :href="route('contact')" as="a" variant="ghost" size="lg" class="!text-ink-50 hover:!bg-white/10">
                    {{ __('nav.contact') }}
                </x-button>
                <x-button :href="route('get-quote')" as="a" variant="gold" size="lg">
                    {{ __('home.hero_cta') }}
                </x-button>
            </div>
        </div>
    </div>

    @if ($faqsByCategory->isNotEmpty())
        <script type="application/ld+json">{!! json_encode(app(\App\Services\SchemaService::class)->faqPage($faqsByCategory->flatten()), JSON_UNESCAPED_SLASHES) !!}</script>
    @endif
</div>
