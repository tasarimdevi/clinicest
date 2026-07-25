<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('nav.about')],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('About Clinicest') }}</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-600">
            {{ __('Clinicest is an independent, verification-first marketplace for dental treatment in Turkey. We are a neutral broker, not a clinic — we only succeed when your treatment does.') }}
        </p>
    </div>

    {{-- Mission --}}
    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Our mission') }}</h2>
        <p class="mt-3 text-sm leading-relaxed text-ink-700">
            {{ __("Choosing a clinic abroad is daunting: it's hard to know who is genuinely qualified, what the real price will be, and what happens if something goes wrong. Clinicest removes that fear by curating only vetted private clinics, showing transparent pricing up front, and guiding you end-to-end — from your first message to your aftercare plan back home.") }}
        </p>
    </div>

    {{-- Trust architecture --}}
    <div class="border-y border-ink-200 bg-white py-14">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('How we build trust') }}</h2>
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2">
                @foreach ([
                    __('Verification badges') => __('A published, documented standard — license check, sterilization/ISO, dentist credentials, and an on-site or documented audit. See "How It Works" for the full checklist.'),
                    __('Transparent pricing') => __('Price ranges are shown up front. No quote-on-request, no on-site upsell surprises.'),
                    __('Real reviews only') => __('Reviews are tied to actual treatment cases and moderated — never fabricated, never AI-generated.'),
                    __('Doctor-level transparency') => __('Named, credentialed dentists with their own profiles — not an anonymous clinic brand.'),
                ] as $title => $body)
                    <div>
                        <p class="font-semibold text-ink-900">{{ $title }}</p>
                        <p class="mt-1 text-sm text-ink-600">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Stats — real, computed counts only --}}
    <div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 gap-4 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <div>
                <p class="font-mono text-2xl font-semibold text-ink-900">{{ $verifiedClinicsCount }}</p>
                <p class="mt-1 text-xs text-ink-500">{{ __('Verified clinics') }}</p>
            </div>
            <div>
                <p class="font-mono text-2xl font-semibold text-ink-900">{{ $targetCountriesCount }}</p>
                <p class="mt-1 text-xs text-ink-500">{{ __('Countries served') }}</p>
            </div>
            <div>
                <p class="font-mono text-2xl font-semibold text-ink-900">{{ $reviewsCount }}</p>
                <p class="mt-1 text-xs text-ink-500">{{ __('Verified patient reviews') }}</p>
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
