<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
        <p class="text-sm text-ink-500">{{ __('Open Leads') }}</p>
        <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $openLeads }}</p>
    </div>
    <div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
        <p class="text-sm text-ink-500">{{ __('New Leads') }}</p>
        <p class="mt-2 text-3xl font-semibold text-brand-700">{{ $newLeads }}</p>
    </div>
    <div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
        <p class="text-sm text-ink-500">{{ __('Won') }}</p>
        <p class="mt-2 text-3xl font-semibold text-success-600">{{ $wonLeads }}</p>
    </div>
    <div class="rounded-lg border border-ink-200 bg-white p-5 shadow-card">
        <p class="text-sm text-ink-500">{{ __('Active Clinics') }}</p>
        <p class="mt-2 text-3xl font-semibold text-ink-900">{{ $activeClinics }}</p>
    </div>

    <div class="col-span-full mt-4">
        <a href="{{ route('admin.leads.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">
            {{ __('View lead inbox') }} &rarr;
        </a>
    </div>
</div>
