<div>
@if ($clinic->application_status === 'pending')
    <div class="mb-6 rounded-lg border border-gold-300/60 bg-gold-50 p-4 text-sm text-ink-700">
        {{ __('Your application is under review. Our team checks every clinic against our verification standard before it goes live — this usually takes a few days. We\'ll email you as soon as there\'s an update.') }}
    </div>
@elseif ($clinic->application_status === 'rejected')
    <div class="mb-6 rounded-lg border border-danger-500/30 bg-danger-500/5 p-4 text-sm text-ink-700">
        <p class="font-medium text-danger-600">{{ __('Your application was not approved.') }}</p>
        @if ($clinic->rejection_reason)
            <p class="mt-1">{{ $clinic->rejection_reason }}</p>
        @endif
        <p class="mt-2">
            {{ __('Questions?') }}
            <a href="{{ route('contact') }}" class="font-medium text-brand-700 hover:underline">{{ __('nav.contact') }}</a>
        </p>
    </div>
@endif

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
        <p class="text-sm text-ink-500">{{ __('Pending Leads') }}</p>
        <p class="mt-2 text-3xl font-semibold text-brand-700">{{ $pendingAssignments }}</p>
    </div>
    <div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
        <p class="text-sm text-ink-500">{{ __('Accepted') }}</p>
        <p class="mt-2 text-3xl font-semibold text-success-600">{{ $acceptedAssignments }}</p>
    </div>
    <div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
        <p class="text-sm text-ink-500">{{ __('Verification') }}</p>
        <p class="mt-2"><x-verification-badge :tier="$verificationTier->value" /></p>
    </div>
    <div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
        <p class="text-sm text-ink-500">{{ __('Rating') }}</p>
        <p class="mt-2 text-3xl font-semibold text-ink-900">★ {{ number_format($rating, 1) }}</p>
    </div>

    <div class="col-span-full mt-4">
        <a href="{{ route('clinic.leads', $clinic) }}" class="text-sm font-semibold text-brand-700 hover:underline">
            {{ __('View lead inbox') }} &rarr;
        </a>
    </div>
</div>
</div>
