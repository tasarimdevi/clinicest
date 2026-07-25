<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('AI Cost Estimator')],
        ]" />

        <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">
            {{ __('AI-assisted · Informational only') }}
        </p>
        <h1 class="mt-2 font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('AI Cost Estimator') }}</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-600">
            {{ __('Pick a treatment and your home country for an instant, honest price band — built from our own clinic and market pricing data, not a guess.') }}
        </p>
    </div>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Treatment') }}</label>
                    <select wire:model.live="treatment_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        <option value="">{{ __('Select a treatment') }}</option>
                        @foreach ($treatments as $t)
                            <option value="{{ $t->id }}">{{ $t->getTranslation('name', app()->getLocale()) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Your country') }}</label>
                    <select wire:model.live="country_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        <option value="">{{ __('Select your country (optional)') }}</option>
                        @foreach ($countries as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if ($selectedTreatment && $estimate)
                @if ($estimate['source'] === null)
                    <p class="mt-6 text-sm text-ink-500">
                        {{ __('No price data available yet for this treatment.') }}
                    </p>
                @else
                    <div class="mt-6 grid grid-cols-1 gap-4 {{ $estimate['local_min'] !== null ? 'sm:grid-cols-3' : 'sm:grid-cols-1' }}">
                        @if ($estimate['local_min'] !== null)
                            <div class="rounded-md bg-ink-50 p-4">
                                <p class="font-mono text-xs uppercase tracking-wide text-ink-400">
                                    {{ __('In :country', ['country' => $selectedCountry->name]) }}
                                </p>
                                <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-ink-900">
                                    {{ $estimate['currency'] }} {{ number_format($estimate['local_min'] / 100, 0) }}–{{ number_format($estimate['local_max'] / 100, 0) }}
                                </p>
                            </div>
                        @endif
                        <div class="rounded-md bg-teal-50 p-4">
                            <p class="font-mono text-xs uppercase tracking-wide text-teal-600">{{ __('In Turkey') }}</p>
                            <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-teal-600">
                                {{ $estimate['currency'] }} {{ number_format($estimate['turkey_min'] / 100, 0) }}–{{ number_format($estimate['turkey_max'] / 100, 0) }}
                            </p>
                        </div>
                        @if ($estimate['savings_pct'] !== null)
                            <div class="rounded-md bg-gold-50 p-4">
                                <p class="font-mono text-xs uppercase tracking-wide text-gold-600">{{ __('You save') }}</p>
                                <p class="mt-1 font-mono text-lg font-semibold tabular-nums text-gold-700">
                                    ~{{ $estimate['savings_pct'] }}%
                                </p>
                            </div>
                        @endif
                    </div>

                    @if ($estimate['source'] === 'treatment_base' && ! $selectedCountry)
                        <p class="mt-4 text-xs text-ink-500">
                            {{ __('Select your country above to see a side-by-side comparison with local prices.') }}
                        </p>
                    @elseif ($estimate['source'] === 'treatment_base' && $selectedCountry)
                        <p class="mt-4 text-xs text-ink-500">
                            {{ __('We don\'t yet have a local price comparison for this pairing — showing the Turkey price only.') }}
                        </p>
                    @endif

                    <p class="mt-4 text-xs text-ink-500">
                        {{ __('This is an instant estimate, not a quote. Your matched clinic confirms an exact price in writing after reviewing your case — always confirm with a dentist before deciding.') }}
                    </p>

                    <x-button :href="route('get-quote', array_filter(['treatment' => $selectedTreatment->id, 'country' => $selectedCountry?->id]))" as="a" size="lg" class="mt-6">
                        {{ __('Get my exact price') }}
                    </x-button>
                @endif
            @endif
        </div>
    </div>
</div>
