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
