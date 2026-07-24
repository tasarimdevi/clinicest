<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('nav.treatments'), 'url' => route('treatments.index')],
            ['name' => $treatment->getTranslation('name', app()->getLocale())],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">
            {{ __(':treatment Cost — Turkey vs. Home', ['treatment' => $treatment->getTranslation('name', app()->getLocale())]) }}
        </h1>

        @if ($treatment->base_price_min)
            <p class="mt-4 font-mono text-lg font-semibold text-teal-600 tabular-nums">
                {{ __('Turkey price from') }} {{ $treatment->currency }} {{ number_format($treatment->base_price_min / 100, 0) }}
            </p>
        @endif

        <x-button :href="route('get-quote', ['treatment' => $treatment->id])" as="a" size="lg" class="mt-5">
            {{ __('home.hero_cta') }}
        </x-button>
    </div>

    {{-- Savings calculator --}}
    @if ($countryTreatments->isNotEmpty())
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Savings calculator') }}</h2>
                <label class="mt-4 block text-sm font-medium text-ink-700">{{ __('Where are you travelling from?') }}</label>
                <select wire:model.live="selected_country_id" class="mt-1.5 w-full max-w-xs rounded-md border-ink-300 text-sm">
                    @foreach ($countryTreatments as $ct)
                        <option value="{{ $ct->country_id }}">{{ $ct->country->name }}</option>
                    @endforeach
                </select>

                @if ($selected)
                    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-md bg-ink-50 p-4">
                            <p class="font-mono text-xs uppercase tracking-wide text-ink-400">
                                {{ __('In :country', ['country' => $selected->country->name]) }}
                            </p>
                            <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-ink-900">
                                {{ $selected->currency }} {{ number_format($selected->local_price_min / 100, 0) }}–{{ number_format($selected->local_price_max / 100, 0) }}
                            </p>
                        </div>
                        <div class="rounded-md bg-teal-50 p-4">
                            <p class="font-mono text-xs uppercase tracking-wide text-teal-600">{{ __('In Turkey') }}</p>
                            <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-teal-600">
                                {{ $selected->currency }} {{ number_format($selected->turkey_price_min / 100, 0) }}–{{ number_format($selected->turkey_price_max / 100, 0) }}
                            </p>
                        </div>
                        <div class="rounded-md bg-gold-50 p-4">
                            <p class="font-mono text-xs uppercase tracking-wide text-gold-600">{{ __('You save') }}</p>
                            <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-gold-700">
                                ~{{ $selected->savingsPct() }}%
                            </p>
                        </div>
                    </div>
                @endif
                <p class="mt-4 text-xs text-ink-500">
                    {{ __('Prices are indicative ranges, not quotes. Your matched clinic confirms an exact price in writing before any commitment.') }}
                </p>
            </div>
        </div>
    @endif

    {{-- Factors affecting price --}}
    <div class="border-y border-ink-200 bg-white py-14">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('What affects the price') }}</h2>
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                @foreach ([
                    __('Case complexity') => __('The number of teeth involved, bone quality, and whether grafting is needed.'),
                    __('Materials & brand') => __('Implant and material brands vary in cost — your clinic will specify exactly what is used.'),
                    __('Clinic verification tier') => __('Elite-tier clinics with more in-house specialists may price slightly higher than standard-tier clinics.'),
                    __('Number of visits') => __('Some cases are completed in one trip; complex cases may need a short follow-up visit.'),
                ] as $title => $body)
                    <div>
                        <p class="font-semibold text-ink-900">{{ $title }}</p>
                        <p class="mt-1 text-sm text-ink-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Is it worth it — honesty section --}}
    <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Is it worth it?') }}</h2>
        <p class="mt-3 text-sm text-ink-600">
            {{ __('Lower cost does not mean lower quality — Turkey\'s dental sector treats a large volume of international patients and many clinics hold international accreditations. But travelling for treatment is a real commitment: budget for recovery time, follow-up care with your home dentist, and the possibility of a second trip for complex cases. Clinicest only lists clinics that pass a documented verification check, and we never fabricate reviews or before/after results.') }}
        </p>
    </div>

    {{-- Clinics offering this treatment --}}
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

        <div class="mt-14 rounded-2xl bg-brand-950 px-8 py-12 text-center text-ink-50">
            <h2 class="font-serif text-2xl font-medium">{{ __('home.final_cta_title') }}</h2>
            <p class="mt-2 text-ink-300">{{ __('home.final_cta_subtitle') }}</p>
            <x-button :href="route('get-quote', ['treatment' => $treatment->id])" as="a" variant="gold" size="lg" class="mt-6">
                {{ __('home.hero_cta') }}
            </x-button>
        </div>
    </div>
</div>
