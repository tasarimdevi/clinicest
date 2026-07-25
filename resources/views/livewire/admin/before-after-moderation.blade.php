<div>
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($cases as $case)
            <div wire:key="ba-{{ $case->id }}" class="overflow-hidden rounded-lg border border-ink-200 bg-white shadow-card">
                <div class="grid grid-cols-2">
                    <img src="{{ $case->before_url }}" alt="{{ __('Before') }}" class="aspect-square w-full object-cover">
                    <img src="{{ $case->after_url }}" alt="{{ __('After') }}" class="aspect-square w-full object-cover">
                </div>
                <div class="p-4">
                    <p class="text-sm font-semibold text-ink-900">{{ $case->getTranslation('title', 'en') }}</p>
                    <p class="mt-1 text-xs text-ink-500">
                        {{ $case->clinic?->getTranslation('name', 'en') }}
                        &middot; {{ $case->treatment?->getTranslation('name', 'en') }}
                        @if ($case->patientCountry)&middot; {{ $case->patientCountry->name }}@endif
                    </p>
                    @if ($case->consent_confirmed)
                        <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-[0.68rem] font-semibold text-teal-600">
                            ✓ {{ __('Consent attested') }}
                        </span>
                    @endif
                    <div class="mt-3 flex gap-3">
                        <button type="button" wire:click="approve({{ $case->id }})" class="text-sm font-medium text-success-600 hover:underline">
                            {{ __('Approve & publish') }}
                        </button>
                        <button type="button" wire:click="reject({{ $case->id }})" wire:confirm="{{ __('Reject and delete this case?') }}"
                                class="text-sm font-medium text-danger-500 hover:underline">
                            {{ __('Reject') }}
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full py-8 text-center text-sm text-ink-500">{{ __('No cases awaiting moderation.') }}</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $cases->links() }}
    </div>
</div>
