<div>
    {{-- Hero — the header/hero band is a deliberate always-dark zone
         (brand-950), independent of light/dark theme. See
         docs/03-design-system.md §1 and the published homepage prototype. --}}
    <section class="relative overflow-hidden bg-brand-950 text-ink-50">
        <div class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-400">
                {{ __('home.hero_overline') }}
            </p>
            <h1 class="mt-4 font-serif text-4xl font-medium leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                {{ __('home.hero_title') }}
            </h1>
            <p class="mt-6 max-w-xl text-lg text-ink-300">
                {{ __('home.hero_subtitle') }}
            </p>

            <div class="mt-10 flex flex-wrap gap-4">
                <x-button :href="route('get-quote')" as="a" variant="gold" size="lg">
                    {{ __('home.hero_cta') }}
                </x-button>
                <x-button :href="route('clinics.index')" as="a" variant="ghost" size="lg" class="!text-ink-50 hover:!bg-white/10">
                    {{ __('See how verification works') }}
                </x-button>
            </div>

            {{-- Boarding-pass detail strip --}}
            <div class="mt-11 flex max-w-lg overflow-hidden rounded-md border border-white/15 bg-brand-900">
                <div class="flex-1 p-5">
                    <div class="font-mono text-base font-semibold tracking-wide">
                        LON <span class="text-gold-400">✈ ✈ ✈</span> IST
                    </div>
                    <div class="mt-2 flex gap-5 text-xs text-ink-300">
                        <span>{{ __('home.bp_stay') }} <b class="font-semibold text-ink-50">{{ __('home.bp_stay_value') }}</b></span>
                        <span>{{ __('home.bp_consult') }} <b class="font-semibold text-ink-50">{{ __('home.bp_consult_value') }}</b></span>
                    </div>
                </div>
                <div class="flex w-32 flex-col justify-center gap-1 border-l border-dashed border-white/15 p-4">
                    <span class="text-[0.62rem] uppercase tracking-wide text-ink-300">{{ __('home.bp_saving') }}</span>
                    <span class="font-mono text-xl font-bold tabular-nums text-teal-300">{{ __('home.bp_saving_value') }}</span>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-ink-300">
                <span class="flex items-center gap-1.5">
                    <x-verification-badge tier="verified" class="!bg-white/10 !text-ink-50" />
                    {{ __('home.trust_verified_clinics') }}
                </span>
                <span class="font-mono tabular-nums">1,240+ {{ __('home.trust_treatments') }}</span>
                <span class="font-mono tabular-nums">★ 4.9</span>
                <span>{{ __('home.trust_gdpr') }}</span>
            </div>
        </div>
    </section>

    {{-- Treatment manifest --}}
    <section id="treatments" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('home.treatments_eyebrow') }}</p>
        <h2 class="mt-2 font-serif text-2xl font-medium text-ink-900 sm:text-3xl">{{ __('nav.treatments') }}</h2>
        <p class="mt-3 max-w-2xl text-ink-600">{{ __('home.treatments_subtitle') }}</p>

        <div class="mt-8 grid grid-cols-1 gap-px overflow-hidden rounded-lg border border-ink-200 bg-ink-200 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredTreatments as $treatment)
                <x-treatment-card :treatment="$treatment" />
            @empty
                <p class="col-span-full bg-white p-6 text-sm text-ink-500">
                    {{ __('No treatments published yet — run the seeder to populate sample data.') }}
                </p>
            @endforelse
        </div>
    </section>

    {{-- How it works --}}
    <section class="border-y border-ink-200 bg-white py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="font-serif text-2xl font-medium text-ink-900 sm:text-3xl">{{ __('home.how_it_works_title') }}</h2>
            <div class="mt-8 grid gap-8 sm:grid-cols-3">
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
    </section>

    {{-- AI Cost Estimator teaser --}}
    <section class="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-gold-300/50 bg-gold-50 px-8 py-8 sm:flex-row sm:items-center">
            <div>
                <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('AI-assisted') }}</p>
                <h2 class="mt-1 font-serif text-xl font-medium text-ink-900">{{ __('Not sure what it will cost?') }}</h2>
                <p class="mt-1 text-sm text-ink-600">{{ __('Get an instant, honest price band for any treatment in seconds.') }}</p>
            </div>
            <x-button :href="route('cost-estimator')" as="a" variant="secondary" size="lg" class="flex-shrink-0">
                {{ __('Try the AI Cost Estimator') }}
            </x-button>
        </div>
    </section>

    {{-- Featured clinics --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="font-serif text-2xl font-medium text-ink-900 sm:text-3xl">{{ __('home.featured_clinics_title') }}</h2>
        <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredClinics as $clinic)
                <x-clinic-card :clinic="$clinic" />
            @empty
                <p class="col-span-full text-sm text-ink-500">
                    {{ __('No clinics published yet — run the seeder to populate sample data.') }}
                </p>
            @endforelse
        </div>
    </section>

    {{-- Final CTA — tear-off ticket stub --}}
    <section class="mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-brand-950 px-8 py-14 text-center text-ink-50 sm:px-14">
            <h2 class="font-serif text-2xl font-medium sm:text-3xl">{{ __('home.final_cta_title') }}</h2>
            <p class="mt-3 text-ink-300">{{ __('home.final_cta_subtitle') }}</p>
            <x-button :href="route('get-quote')" as="a" variant="gold" size="lg" class="mt-8">
                {{ __('home.hero_cta') }}
            </x-button>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 border-t-2 border-dashed border-white/15"></div>
        </div>
    </section>
</div>
