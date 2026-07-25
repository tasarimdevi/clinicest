<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-lg font-semibold text-ink-900">{{ __('Send an offer to :name', ['name' => $lead->full_name]) }}</h1>
        <p class="mt-1 text-sm text-ink-500">
            {{ $lead->primaryTreatment?->getTranslation('name', app()->getLocale()) ?? __('No treatment specified') }}
            &middot; {{ $lead->email }}
        </p>
    </div>

    @if ($sent)
        <div class="rounded-lg border border-success-500/30 bg-success-500/5 p-6">
            <p class="font-semibold text-success-600">{{ __('Offer sent.') }}</p>
            <p class="mt-2 text-sm text-ink-600">{{ __('The patient and Clinicest CRM have been notified.') }}</p>
            <a href="{{ route('clinic.leads', ['clinic' => $clinic->id]) }}" class="mt-4 inline-block text-sm font-medium text-brand-700 hover:underline">
                {{ __('Back to lead inbox') }} &rarr;
            </a>
        </div>
    @else
        <form wire:submit="send" class="space-y-6">
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Offer details') }}</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Title') }}</label>
                        <input type="text" wire:model="title" placeholder="{{ __('e.g. All-on-4 Full Arch Treatment Plan') }}" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('title') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Doctor (optional)') }}</label>
                        <select wire:model="doctor_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="">{{ __('Unassigned') }}</option>
                            @foreach ($doctors as $doctor)
                                <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Valid until') }}</label>
                        <input type="date" wire:model="valid_until" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('valid_until') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Treatment plan notes (optional)') }}</label>
                        <textarea wire:model="treatment_plan" rows="4" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                        @error('treatment_plan') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Treatments & pricing') }}</h2>
                @error('selected') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                <div class="mt-4 divide-y divide-ink-100">
                    @forelse ($treatments as $treatment)
                        <div class="flex items-center gap-4 py-3">
                            <input type="checkbox" wire:model="selected.{{ $treatment->id }}" class="rounded border-ink-300">
                            <span class="flex-1 text-sm text-ink-800">{{ $treatment->getTranslation('name', app()->getLocale()) }}</span>
                            <div class="flex items-center gap-1">
                                <span class="text-xs text-ink-500">{{ $treatment->pivot->currency }}</span>
                                <input type="number" step="0.01" min="0" wire:model="prices.{{ $treatment->id }}" class="w-28 rounded-md border-ink-300 text-sm">
                            </div>
                        </div>
                    @empty
                        <p class="py-3 text-sm text-ink-500">{{ __('This clinic has no treatments configured yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Includes') }}</h2>
                <div class="mt-4 flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" wire:model="includes_hotel" class="rounded border-ink-300">
                        {{ __('Hotel') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" wire:model="includes_transfer" class="rounded border-ink-300">
                        {{ __('Airport transfer') }}
                    </label>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-ink-700">{{ __('Warranty (years)') }}</label>
                        <input type="number" min="0" max="10" wire:model="warranty_years" class="w-20 rounded-md border-ink-300 text-sm">
                    </div>
                </div>
                @error('warranty_years') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>

            <x-button type="submit" size="lg" wire:loading.attr="disabled" wire:target="send">
                <span wire:loading.remove wire:target="send">{{ __('Send offer') }}</span>
                <span wire:loading wire:target="send">{{ __('Sending…') }}</span>
            </x-button>
        </form>
    @endif

    @if ($existingOffers->isNotEmpty())
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('Previously sent to this patient') }}</h2>
            <ul class="mt-3 divide-y divide-ink-100">
                @foreach ($existingOffers as $offer)
                    <li class="flex items-center justify-between py-3 text-sm">
                        <div>
                            <p class="font-medium text-ink-800">{{ $offer->title }}</p>
                            <p class="text-xs text-ink-500">{{ $offer->created_at->format('d M Y H:i') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono tabular-nums text-ink-700">
                                {{ $offer->currency }} {{ number_format($offer->price_total / 100, 0) }}
                            </span>
                            <span class="inline-flex rounded-full bg-ink-100 px-2.5 py-1 text-xs font-medium text-ink-700">
                                {{ $offer->status->label() }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
