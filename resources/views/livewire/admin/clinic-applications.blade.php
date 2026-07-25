<div>
    <div class="flex items-center justify-between">
        <p class="text-sm text-ink-500">{{ __(':count application(s) awaiting review.', ['count' => $applications->count()]) }}</p>
        <a href="{{ route('admin.clinics.index') }}" class="text-sm font-medium text-brand-600 hover:underline">
            {{ __('View all clinics') }} &rarr;
        </a>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($applications as $clinic)
            <div wire:key="application-{{ $clinic->id }}" class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 class="font-serif text-lg font-medium text-ink-900">{{ $clinic->getTranslation('name', 'en') }}</h3>
                        <p class="mt-1 text-sm text-ink-500">
                            {{ $clinic->city?->name }} &middot; {{ __('Applied') }} {{ $clinic->applied_at?->format('d M Y') }}
                        </p>
                        @if ($clinic->owner)
                            <p class="mt-1 text-sm text-ink-600">{{ $clinic->owner->name }} &middot; {{ $clinic->owner->email }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <x-button wire:click="approve({{ $clinic->id }})" size="sm">{{ __('Approve') }}</x-button>
                        <x-button wire:click="startReject({{ $clinic->id }})" variant="ghost" size="sm">{{ __('Reject') }}</x-button>
                    </div>
                </div>

                @if ($clinic->about)
                    <p class="mt-4 text-sm text-ink-700">{{ $clinic->getTranslation('about', 'en') }}</p>
                @endif

                @if ($clinic->application_message)
                    <div class="mt-4 rounded-md bg-ink-50 p-4 text-sm text-ink-700">
                        {{ $clinic->application_message }}
                    </div>
                @endif

                @if ($clinic->credentials_url)
                    <a href="{{ $clinic->credentials_url }}" target="_blank" rel="noopener" class="mt-3 inline-block text-sm font-medium text-brand-700 hover:underline">
                        {{ __('View credentials link') }} &rarr;
                    </a>
                @endif

                @if ($rejecting[$clinic->id] ?? false)
                    <div class="mt-4 rounded-md border border-danger-500/30 bg-danger-500/5 p-4">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Reason (sent to the applicant)') }}</label>
                        <textarea wire:model="rejectReason.{{ $clinic->id }}" rows="2" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                        <x-button wire:click="reject({{ $clinic->id }})" variant="ghost" size="sm" class="mt-2">{{ __('Confirm rejection') }}</x-button>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-ink-300 bg-ink-50 p-8 text-center text-sm text-ink-500">
                {{ __('No applications waiting for review.') }}
            </div>
        @endforelse
    </div>
</div>
