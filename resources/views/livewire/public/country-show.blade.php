<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => $country->name],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">
            {{ __('Dental Treatment in Turkey for :country Patients', ['country' => $country->name]) }}
        </h1>

        @if ($countryTreatments->isNotEmpty())
            @php
                $avgSavings = (int) round($countryTreatments->avg(fn ($ct) => $ct->savingsPct()));
            @endphp
            <p class="mt-4 font-mono text-lg font-semibold text-teal-600 tabular-nums">
                {{ __('Patients from :country typically save around :pct% versus local private prices.', ['country' => $country->name, 'pct' => $avgSavings]) }}
            </p>
        @endif

        <x-button :href="route('get-quote', ['country' => $country->id])" as="a" size="lg" class="mt-5">
            {{ __('home.hero_cta') }}
        </x-button>
    </div>

    {{-- Savings comparison table --}}
    @if ($countryTreatments->isNotEmpty())
        <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Cost comparison') }}</h2>
            <div class="mt-4 overflow-hidden rounded-lg border border-ink-200 bg-white shadow-card">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-ink-200 bg-ink-50 text-xs uppercase tracking-wide text-ink-500">
                        <tr>
                            <th class="px-4 py-3">{{ __('Treatment') }}</th>
                            <th class="px-4 py-3">{{ __('In :country', ['country' => $country->name]) }}</th>
                            <th class="px-4 py-3">{{ __('In Turkey') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('You save') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-200">
                        @foreach ($countryTreatments as $ct)
                            <tr>
                                <td class="px-4 py-3 font-medium text-ink-900">
                                    <a href="{{ route('treatments.show', $ct->treatment->slug) }}" class="hover:underline">
                                        {{ $ct->treatment->getTranslation('name', app()->getLocale()) }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 font-mono tabular-nums text-ink-600">
                                    {{ $ct->currency }} {{ number_format($ct->local_price_min / 100, 0) }}–{{ number_format($ct->local_price_max / 100, 0) }}
                                </td>
                                <td class="px-4 py-3 font-mono tabular-nums text-teal-600">
                                    {{ $ct->currency }} {{ number_format($ct->turkey_price_min / 100, 0) }}–{{ number_format($ct->turkey_price_max / 100, 0) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-semibold tabular-nums text-gold-600">
                                    {{ $ct->savingsPct() }}%
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-3 text-xs text-ink-500">
                {{ __('Prices are indicative ranges, not quotes. Your matched clinic confirms an exact price in writing before any commitment.') }}
            </p>
        </div>
    @endif

    {{-- Travel info --}}
    <div class="border-y border-ink-200 bg-white py-14">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Planning your trip') }}</h2>
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                @if ($country->flight_note)
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Flights') }}</p>
                        <p class="mt-1 text-sm text-ink-700">{{ $country->flight_note }}</p>
                        @if ($country->avg_flight_hours)
                            <p class="mt-1 text-xs text-ink-500">{{ __('~:hours hrs average flight time', ['hours' => $country->avg_flight_hours]) }}</p>
                        @endif
                    </div>
                @endif
                @if ($country->visa_info)
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Visa') }}</p>
                        <p class="mt-1 text-sm text-ink-700">{{ $country->visa_info }}</p>
                    </div>
                @endif
                @if ($country->best_time_to_visit)
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Best time to visit') }}</p>
                        <p class="mt-1 text-sm text-ink-700">{{ $country->best_time_to_visit }}</p>
                    </div>
                @endif
                @if ($country->primary_language)
                    <div>
                        <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Clinic language match') }}</p>
                        <p class="mt-1 text-sm text-ink-700">{{ __('We prioritise clinics with :lang-speaking staff for patients from :country.', ['lang' => strtoupper($country->primary_language), 'country' => $country->name]) }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- How it works --}}
    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('home.how_it_works_title') }}</h2>
        <div class="mt-6 grid gap-8 sm:grid-cols-3">
            @foreach ([
                ['n' => '01', 't' => __('Tell us your needs')],
                ['n' => '02', 't' => __('Get matched offers')],
                ['n' => '03', 't' => __('Fly & smile')],
            ] as $step)
                <div class="border-t-2 border-gold-500 pt-4">
                    <span class="font-mono text-xs font-semibold tracking-wide text-gold-600">STEP {{ $step['n'] }}</span>
                    <p class="mt-2 text-base font-semibold text-ink-900">{{ $step['t'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Clinics serving this country --}}
    @if ($clinics->isNotEmpty())
        <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <h2 class="font-serif text-xl font-medium text-ink-900">
                {{ __('Clinics serving patients from :country', ['country' => $country->name]) }}
            </h2>
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
            <x-button :href="route('get-quote', ['country' => $country->id])" as="a" variant="gold" size="lg" class="mt-6">
                {{ __('home.hero_cta') }}
            </x-button>
        </div>
    </div>
</div>
