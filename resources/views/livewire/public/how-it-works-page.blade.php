<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('nav.how_it_works')],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('nav.how_it_works') }}</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-600">
            {{ __('From first message to your written treatment plan — here is exactly what happens, and how we vet every clinic before it ever reaches you.') }}
        </p>
    </div>

    {{-- Steps --}}
    <div class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ([
                ['n' => '01', 't' => __('Tell us your case'), 'd' => __('Share your treatment needs, photos or an x-ray if you have them, and your timeline — takes about 2 minutes.')],
                ['n' => '02', 't' => __('We match you to verified clinics'), 'd' => __('Only clinics that pass our verification standard below are eligible to be matched to your case.')],
                ['n' => '03', 't' => __('Get a written plan'), 'd' => __('Your matched clinic reviews your case and replies with an exact price and treatment plan — no on-site surprises.')],
                ['n' => '04', 't' => __('Travel with support'), 'd' => __('Once you accept a plan, we help you organise the trip. You keep talking directly to your clinic throughout.')],
                ['n' => '05', 't' => __('Treatment & aftercare'), 'd' => __('Your clinic treats you in Istanbul and gives you an aftercare plan to bring back to your home dentist.')],
            ] as $step)
                <div class="border-t-2 border-gold-500 pt-4">
                    <span class="font-mono text-xs font-semibold tracking-wide text-gold-600">STEP {{ $step['n'] }}</span>
                    <p class="mt-2 text-base font-semibold text-ink-900">{{ $step['t'] }}</p>
                    <p class="mt-1 text-sm text-ink-600">{{ $step['d'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Verification standard --}}
    <div class="border-y border-ink-200 bg-white py-14">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Our verification standard') }}</h2>
            <p class="mt-3 text-sm text-ink-600">
                {{ __('Every clinic on Clinicest is checked against a documented standard before being listed — and the tier badge on their profile tells you exactly what has been confirmed.') }}
            </p>
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ([
                    __('Valid dental practice license on file'),
                    __('Sterilization / hygiene standard confirmed'),
                    __('Named, credentialed dentists on staff'),
                    __('On-site or documented audit before listing'),
                ] as $point)
                    <div class="flex items-start gap-2 text-sm text-ink-700">
                        <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0l-3.5-3.5a1 1 0 1 1 1.4-1.4l2.8 2.8 6.8-6.8a1 1 0 0 1 1.4 0Z" clip-rule="evenodd" />
                        </svg>
                        {{ $point }}
                    </div>
                @endforeach
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                @foreach ($tiers as $tier)
                    <x-verification-badge :tier="$tier->value" />
                @endforeach
            </div>
            <p class="mt-3 text-xs text-ink-500">
                {{ __('Pending clinics are not shown publicly — a badge only appears once a clinic passes the standard.') }}
            </p>
        </div>
    </div>

    {{-- Trust proof --}}
    <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-4 rounded-lg border border-ink-200 bg-white p-6 shadow-card sm:grid-cols-2">
            <div>
                <p class="font-mono text-3xl font-semibold text-ink-900">{{ $verifiedClinicsCount }}</p>
                <p class="mt-1 text-sm text-ink-500">{{ __('Active verified clinics') }}</p>
            </div>
            <div>
                <p class="font-mono text-3xl font-semibold text-ink-900">{{ $publishedTreatmentsCount }}</p>
                <p class="mt-1 text-sm text-ink-500">{{ __('Treatments covered') }}</p>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-4xl px-4 pb-14 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-brand-950 px-8 py-12 text-center text-ink-50">
            <h2 class="font-serif text-2xl font-medium">{{ __('home.final_cta_title') }}</h2>
            <p class="mt-2 text-ink-300">{{ __('home.final_cta_subtitle') }}</p>
            <x-button :href="route('get-quote')" as="a" variant="gold" size="lg" class="mt-6">
                {{ __('home.hero_cta') }}
            </x-button>
        </div>
    </div>
</div>
