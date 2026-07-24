<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('nav.treatments'), 'url' => route('treatments.index')],
            ['name' => $treatment->getTranslation('name', app()->getLocale())],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">
            {{ $treatment->getTranslation('name', app()->getLocale()) }} — {{ __('Cost, Procedure & Best Clinics') }}
        </h1>

        @if ($treatment->base_price_min)
            <p class="mt-4 font-mono text-lg font-semibold text-teal-600 tabular-nums">
                {{ __('from') }} {{ $treatment->currency }} {{ number_format($treatment->base_price_min / 100, 0) }}
            </p>
        @endif

        <x-button :href="route('get-quote', ['treatment' => $treatment->id])" as="a" size="lg" class="mt-5">
            {{ __('home.hero_cta') }}
        </x-button>

        {{-- Quick facts --}}
        <div class="mt-8 grid grid-cols-2 gap-4 rounded-lg border border-ink-200 bg-white p-6 shadow-card sm:grid-cols-4">
            <div>
                <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Duration') }}</p>
                <p class="mt-1 font-semibold text-ink-900">
                    {{ $treatment->avg_duration_min ? gmdate('H:i', $treatment->avg_duration_min * 60) : '—' }}
                </p>
            </div>
            <div>
                <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Recovery') }}</p>
                <p class="mt-1 font-semibold text-ink-900">
                    {{ $treatment->recovery_days ? $treatment->recovery_days.' '.__('days') : '—' }}
                </p>
            </div>
            <div>
                <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Trips to Turkey') }}</p>
                <p class="mt-1 font-semibold text-ink-900">{{ $treatment->trips_required ?? '—' }}</p>
            </div>
            <div>
                <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Verified clinics') }}</p>
                <p class="mt-1 font-semibold text-ink-900">{{ $clinics->count() }}</p>
            </div>
        </div>

        {{-- Overview --}}
        @if ($treatment->getTranslation('summary', app()->getLocale()) || $treatment->getTranslation('body', app()->getLocale()))
            <div class="prose mt-10 max-w-none text-ink-700">
                <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Overview') }}</h2>
                @if ($summary = $treatment->getTranslation('summary', app()->getLocale()))
                    <p class="mt-3">{{ $summary }}</p>
                @endif
                @if ($body = $treatment->getTranslation('body', app()->getLocale()))
                    <p class="mt-3">{{ $body }}</p>
                @endif
            </div>
        @endif

        {{-- Cost --}}
        @if ($treatment->base_price_min || $treatment->base_price_max)
            <div class="mt-10 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Cost') }}</h2>
                <p class="mt-2 font-mono text-2xl font-semibold tabular-nums text-teal-600">
                    {{ $treatment->currency }} {{ number_format($treatment->base_price_min / 100, 0) }}–{{ number_format($treatment->base_price_max / 100, 0) }}
                </p>
                <p class="mt-2 text-sm text-ink-500">
                    {{ __('Final price is confirmed in writing by your matched clinic after reviewing your case — no on-site upsells.') }}
                </p>
                <div class="mt-4 flex flex-wrap gap-3">
                    <x-button :href="route('get-quote', ['treatment' => $treatment->id])" as="a" variant="secondary" size="sm">
                        {{ __('Get exact quote') }}
                    </x-button>
                    <a href="{{ route('cost.show', $treatment->slug) }}" class="inline-flex items-center text-sm font-medium text-brand-700 hover:underline">
                        {{ __('Compare cost vs. your country') }} →
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Before & After --}}
    @if ($beforeAfterCases->isNotEmpty())
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Before & After') }}</h2>
                <a href="{{ route('before-after.index', ['treatment' => $treatment->id]) }}" class="text-sm text-brand-600 hover:underline">
                    {{ __('See all') }}
                </a>
            </div>
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($beforeAfterCases as $case)
                    <x-before-after-card :case="$case" />
                @endforeach
            </div>
        </div>
    @endif

    {{-- Recommended clinics --}}
    @if ($clinics->isNotEmpty())
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Clinics offering this treatment') }}</h2>
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($clinics as $clinic)
                    <x-clinic-card :clinic="$clinic" />
                @endforeach
            </div>
        </div>
    @endif

    <div class="mx-auto max-w-4xl px-4 pb-14 sm:px-6 lg:px-8">
        {{-- FAQ --}}
        @if ($faqs->isNotEmpty())
            <div class="mt-4">
                <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Frequently asked questions') }}</h2>
                <div class="mt-4">
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
            <script type="application/ld+json">{!! json_encode(app(\App\Services\SchemaService::class)->faqPage($faqs), JSON_UNESCAPED_SLASHES) !!}</script>
        @endif

        {{-- Related treatments --}}
        @if ($related->isNotEmpty())
            <div class="mt-14">
                <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Related treatments') }}</h2>
                <div class="mt-6 grid grid-cols-1 gap-px overflow-hidden rounded-lg border border-ink-200 bg-ink-200 sm:grid-cols-3">
                    @foreach ($related as $rel)
                        <x-treatment-card :treatment="$rel" />
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-14 rounded-2xl bg-brand-950 px-8 py-12 text-center text-ink-50">
            <h2 class="font-serif text-2xl font-medium">{{ __('home.final_cta_title') }}</h2>
            <p class="mt-2 text-ink-300">{{ __('home.final_cta_subtitle') }}</p>
            <x-button :href="route('get-quote', ['treatment' => $treatment->id])" as="a" variant="gold" size="lg" class="mt-6">
                {{ __('home.hero_cta') }}
            </x-button>
        </div>
    </div>

    <script type="application/ld+json">{!! json_encode(app(\App\Services\SchemaService::class)->medicalProcedure($treatment), JSON_UNESCAPED_SLASHES) !!}</script>
</div>
