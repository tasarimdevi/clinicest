<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('Terms of Service')],
    ]" />

    <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('Terms of Service') }}</h1>
    <p class="mt-2 text-sm text-ink-500">{{ __('Last updated: :date', ['date' => now()->format('d M Y')]) }}</p>

    <div class="mt-6 rounded-lg border border-gold-300/60 bg-gold-50 p-4 text-sm text-ink-700">
        {{ __('Draft for review — not yet finalized, including the governing-law clause below. A lawyer should review and complete these terms before they are relied on as binding.') }}
    </div>

    <div class="mt-10 space-y-4 text-sm leading-relaxed text-ink-700 [&_a]:text-brand-700 [&_a]:underline [&_h2]:mt-8 [&_h2]:font-serif [&_h2]:text-xl [&_h2]:font-medium [&_h2]:text-ink-900 [&_li]:mt-1 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-5">
        <h2>{{ __('What Clinicest is') }}</h2>
        <p>{{ __('Clinicest is an independent marketplace that connects prospective patients with independently owned and operated private dental clinics in Turkey. Clinicest is not a dental clinic, does not employ dentists, and does not provide dental treatment. Any treatment contract is between you and the clinic you choose — not with Clinicest.') }}</p>

        <h2>{{ __('Not medical advice') }}</h2>
        <p>{{ __('Content on this site — including cost estimates, treatment descriptions, and any AI-assisted tools — is informational only and is not a diagnosis or medical advice. Always confirm your treatment plan, suitability, and risks with a qualified dentist before proceeding.') }}</p>

        <h2>{{ __('Using the site') }}</h2>
        <p>{{ __('You agree to provide accurate information when submitting a quote request or contact form, and not to misuse the site (including submitting false reviews, scraping, or attempting to disrupt the service).') }}</p>

        <h2>{{ __('Pricing and quotes') }}</h2>
        <p>{{ __('Prices shown on Clinicest — including on treatment, cost, and country pages, and in the AI Cost Estimator — are indicative ranges based on our own data, not binding quotes. Your matched clinic confirms an exact price in writing after reviewing your case; that confirmed price, agreed directly with the clinic, is what applies.') }}</p>

        <h2>{{ __('Reviews') }}</h2>
        <p>{{ __('Reviews published on Clinicest must reflect a genuine experience. We do not fabricate reviews, and we moderate submissions before publishing. Submitting a false review may result in removal and loss of access to the site.') }}</p>

        <h2>{{ __('Verification tiers') }}</h2>
        <p>{{ __('A clinic\'s verification badge reflects that it has passed our documented checks (see "How It Works") at the time it was granted. It is not a guarantee of treatment outcome.') }}</p>

        <h2>{{ __('Limitation of liability') }}</h2>
        <p>{{ __('To the fullest extent permitted by law, Clinicest is not liable for the acts, omissions, or treatment outcomes of any independent clinic, and is not a party to any agreement you enter into with a clinic. Clinicest\'s role is limited to matching, information, and facilitation.') }}</p>

        <h2>{{ __('Intellectual property') }}</h2>
        <p>{{ __('The Clinicest name, site design, and original content are owned by Clinicest and may not be reproduced without permission.') }}</p>

        <h2>{{ __('Governing law') }}</h2>
        <p>{{ __('[Governing law and jurisdiction to be added.]') }}</p>

        <h2>{{ __('Changes to these terms') }}</h2>
        <p>{{ __('We may update these terms from time to time. The "Last updated" date above reflects the latest revision.') }}</p>

        <h2>{{ __('Contact') }}</h2>
        <p>
            {{ __('Questions about these terms? Email') }}
            <a href="mailto:{{ config('clinicest.contact_email') }}">{{ config('clinicest.contact_email') }}</a>
            {{ __('or use our') }}
            <a href="{{ route('contact') }}">{{ __('nav.contact') }}</a>
            {{ __('page.') }}
        </p>
    </div>
</div>
