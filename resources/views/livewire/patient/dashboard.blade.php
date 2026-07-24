<div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
    <h2 class="text-sm font-semibold text-ink-900">{{ __('Your requests') }}</h2>

    <ul class="mt-4 divide-y divide-ink-100">
        @forelse ($leads as $lead)
            <li class="flex items-center justify-between py-3 text-sm">
                <span class="text-ink-700">
                    {{ $lead->primaryTreatment?->getTranslation('name', app()->getLocale()) ?? __('General inquiry') }}
                </span>
                <span class="rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                    {{ $lead->status->label() }}
                </span>
            </li>
        @empty
            <li class="py-3 text-sm text-ink-500">{{ __("You haven't submitted a request yet.") }}</li>
        @endforelse
    </ul>

    <x-button :href="route('get-quote')" as="a" size="sm" class="mt-4">{{ __('nav.get_quote') }}</x-button>
</div>
