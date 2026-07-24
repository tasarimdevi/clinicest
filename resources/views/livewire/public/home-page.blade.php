<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-brand-950 via-brand-900 to-brand-800 text-white">
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <p class="text-sm font-semibold uppercase tracking-widest text-brand-300">
                {{ __('home.hero_overline') }}
            </p>
            <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                {{ __('home.hero_title') }}
            </h1>
            <p class="mt-6 max-w-xl text-lg text-brand-100">
                {{ __('home.hero_subtitle') }}
            </p>

            <div class="mt-10 flex flex-col gap-4 rounded-xl border border-white/20 bg-white/10 p-4 backdrop-blur-xl sm:flex-row sm:items-center">
                <select class="w-full rounded-md border-0 bg-white/90 px-4 py-2.5 text-sm text-ink-800 sm:w-auto sm:flex-1">
                    <option>{{ __('nav.treatments') }}</option>
                </select>
                <select class="w-full rounded-md border-0 bg-white/90 px-4 py-2.5 text-sm text-ink-800 sm:w-auto sm:flex-1">
                    <option>{{ __('Country') }}</option>
                </select>
                <x-button :href="route('get-quote')" as="a" size="lg" class="w-full sm:w-auto">
                    {{ __('home.hero_cta') }}
                </x-button>
            </div>

            <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-brand-100">
                <span class="flex items-center gap-1.5">
                    <x-verification-badge tier="verified" class="!bg-white/10 !text-white" />
                    {{ __('home.trust_verified_clinics') }}
                </span>
                <span>1,240+ {{ __('home.trust_treatments') }}</span>
                <span>★ 4.9</span>
                <span>{{ __('home.trust_gdpr') }}</span>
            </div>
        </div>
    </section>

    {{-- Treatment categories --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-semibold text-ink-900 sm:text-3xl">{{ __('nav.treatments') }}</h2>
        <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @forelse ($featuredTreatments as $treatment)
                <x-treatment-card :treatment="$treatment" />
            @empty
                <p class="col-span-full text-sm text-ink-500">
                    {{ __('No treatments published yet — run the seeder to populate sample data.') }}
                </p>
            @endforelse
        </div>
    </section>

    {{-- How it works --}}
    <section class="bg-ink-50 py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-ink-900 sm:text-3xl">{{ __('home.how_it_works_title') }}</h2>
            <div class="mt-8 grid gap-8 sm:grid-cols-3">
                @foreach ([
                    ['n' => '1', 't' => __('Tell us your needs')],
                    ['n' => '2', 't' => __('Get matched offers')],
                    ['n' => '3', 't' => __('Fly & smile')],
                ] as $step)
                    <div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-700 text-sm font-semibold text-white">
                            {{ $step['n'] }}
                        </div>
                        <p class="mt-3 text-sm font-medium text-ink-800">{{ $step['t'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Featured clinics --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-semibold text-ink-900 sm:text-3xl">{{ __('home.featured_clinics_title') }}</h2>
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

    {{-- Final CTA --}}
    <section class="bg-brand-900 py-16 text-white">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold sm:text-3xl">{{ __('home.final_cta_title') }}</h2>
            <p class="mt-3 text-brand-200">{{ __('home.final_cta_subtitle') }}</p>
            <x-button :href="route('get-quote')" as="a" size="lg" class="mt-8">
                {{ __('home.hero_cta') }}
            </x-button>
        </div>
    </section>
</div>
