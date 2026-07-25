<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('Privacy Policy')],
    ]" />

    <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('Privacy Policy') }}</h1>
    <p class="mt-2 text-sm text-ink-500">{{ __('Last updated: :date', ['date' => now()->format('d M Y')]) }}</p>

    <div class="mt-6 rounded-lg border border-gold-300/60 bg-gold-50 p-4 text-sm text-ink-700">
        {{ __('Draft for review — not yet finalized. This page describes what Clinicest actually collects and does today, but the legal entity and registered address below are placeholders. A lawyer should review and complete this policy before it is relied on as binding.') }}
    </div>

    <div class="mt-10 space-y-4 text-sm leading-relaxed text-ink-700 [&_a]:text-brand-700 [&_a]:underline [&_h2]:mt-8 [&_h2]:font-serif [&_h2]:text-xl [&_h2]:font-medium [&_h2]:text-ink-900 [&_li]:mt-1 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-5">
        <h2>{{ __('Who we are') }}</h2>
        <p>{{ __('Clinicest ([legal entity name and registered address to be added]) operates :url, an independent marketplace that connects prospective patients with verified private dental clinics in Turkey. We are the "data controller" for the enquiry data described below.', ['url' => config('app.url')]) }}</p>

        <h2>{{ __('What we collect') }}</h2>
        <p>{{ __('When you submit a free-quote request or contact form, we collect:') }}</p>
        <ul>
            <li>{{ __('Your name, email address, and WhatsApp/phone number (if provided)') }}</li>
            <li>{{ __('The treatment you are interested in, your country, and any timeline or budget you share') }}</li>
            <li>{{ __('Any message, photos, or x-rays you choose to send us') }}</li>
            <li>{{ __('Consent metadata: which version of this policy you agreed to, the date, your IP address, and browser user-agent — kept as proof of consent') }}</li>
            <li>{{ __('Marketing attribution (e.g. which link or campaign brought you to the site), if present in the URL you arrived from') }}</li>
        </ul>

        <h2>{{ __('Why we collect it') }}</h2>
        <p>{{ __('We use your data to: match you with verified clinics that fit your case, respond to your enquiry, and improve our matching quality. We do not sell your data, and we do not use it for purposes beyond running the Clinicest matching service.') }}</p>

        <h2>{{ __('Who we share it with') }}</h2>
        <p>{{ __('We share your enquiry with the clinic(s) we match you to, so they can prepare a treatment plan and price for you. Because our verified clinics are based in Turkey, this means your data is transferred outside the country you live in — we only share what a clinic needs to prepare your quote, and only after you submit your request.') }}</p>

        <h2>{{ __('Legal basis') }}</h2>
        <p>{{ __('We process your data on the basis of your consent, given when you submit the quote or contact form. You can withdraw consent at any time (see "Your rights" below) — this does not affect processing that already happened.') }}</p>

        <h2>{{ __('How long we keep it') }}</h2>
        <p>{{ __('We keep enquiry data for as long as needed to provide the matching service and respond to your request, or until you ask us to erase it, whichever comes first.') }}</p>

        <h2>{{ __('Cookies') }}</h2>
        <p>{{ __('The site currently uses only a session cookie required for the site to function (e.g. keeping a form\'s state while you fill it in). We do not currently run third-party analytics or advertising cookies.') }}</p>

        <h2>{{ __('Your rights') }}</h2>
        <p>
            {{ __('Depending on where you live, you may have the right to access, correct, delete, restrict, or export your data, and to object to or withdraw consent for its processing. See our') }}
            <a href="{{ route('legal.gdpr') }}">{{ __('GDPR') }}</a>
            {{ __('page for how to exercise these rights.') }}
        </p>

        <h2>{{ __('Contact') }}</h2>
        <p>
            {{ __('Questions about this policy? Email') }}
            <a href="mailto:{{ config('clinicest.contact_email') }}">{{ config('clinicest.contact_email') }}</a>
            {{ __('or use our') }}
            <a href="{{ route('contact') }}">{{ __('nav.contact') }}</a>
            {{ __('page.') }}
        </p>
    </div>
</div>
