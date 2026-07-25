<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('GDPR')],
    ]" />

    <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('GDPR — Your Data Rights') }}</h1>
    <p class="mt-2 text-sm text-ink-500">{{ __('Last updated: :date', ['date' => now()->format('d M Y')]) }}</p>

    <div class="mt-6 rounded-lg border border-gold-300/60 bg-gold-50 p-4 text-sm text-ink-700">
        {{ __('Draft for review — not yet finalized, including the data controller and supervisory authority details below. A lawyer should review and complete this page before it is relied on as binding.') }}
    </div>

    <div class="mt-10 space-y-4 text-sm leading-relaxed text-ink-700 [&_a]:text-brand-700 [&_a]:underline [&_h2]:mt-8 [&_h2]:font-serif [&_h2]:text-xl [&_h2]:font-medium [&_h2]:text-ink-900 [&_li]:mt-1 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-5">
        <p>{{ __('This page explains your rights under the General Data Protection Regulation (GDPR) and how to exercise them with Clinicest. See our') }} <a href="{{ route('legal.privacy') }}">{{ __('Privacy Policy') }}</a> {{ __('for what data we collect and why.') }}</p>

        <h2>{{ __('Data controller') }}</h2>
        <p>{{ __('[Legal entity name and registered address to be added.] For any GDPR request, contact us using the details below.') }}</p>

        <h2>{{ __('Your rights') }}</h2>
        <ul>
            <li>{{ __('Right of access — request a copy of the data we hold about you.') }}</li>
            <li>{{ __('Right to rectification — ask us to correct inaccurate or incomplete data.') }}</li>
            <li>{{ __('Right to erasure — ask us to delete your data, where we are not required to keep it.') }}</li>
            <li>{{ __('Right to restrict processing — ask us to limit how we use your data.') }}</li>
            <li>{{ __('Right to data portability — request your data in a portable format.') }}</li>
            <li>{{ __('Right to object — object to processing based on our legitimate interests.') }}</li>
            <li>{{ __('Right to withdraw consent — withdraw consent at any time, without affecting processing that already happened.') }}</li>
        </ul>

        <h2>{{ __('How to exercise your rights') }}</h2>
        <p>
            {{ __('Email') }}
            <a href="mailto:{{ config('clinicest.contact_email') }}">{{ config('clinicest.contact_email') }}</a>
            {{ __('with your request. We will need to verify your identity before acting on it, and we aim to respond within one month, as required by GDPR.') }}
        </p>

        <h2>{{ __('International transfers') }}</h2>
        <p>{{ __('When we match you with a clinic, your enquiry data is shared with that clinic in Turkey so they can prepare your treatment plan. We only share what is needed for that purpose, and only after you submit a request.') }}</p>

        <h2>{{ __('Complaints') }}</h2>
        <p>{{ __('If you are unhappy with how we have handled your data, you have the right to lodge a complaint with your local data protection supervisory authority.') }}</p>
    </div>
</div>
