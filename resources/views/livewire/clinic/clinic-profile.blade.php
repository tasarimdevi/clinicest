<div class="max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-ink-900">{{ __('Clinic Profile') }}</h1>
            <p class="mt-1 text-sm text-ink-500">{{ __('This is what patients see on your public profile.') }}</p>
        </div>
        <a href="{{ route('clinics.show', $clinic->slug) }}" target="_blank" rel="noopener" class="text-sm font-medium text-brand-700 hover:underline">
            {{ __('View public profile') }} &rarr;
        </a>
    </div>

    @if ($saved)
        <div class="rounded-lg border border-success-500/30 bg-success-500/5 p-4 text-sm text-success-600">
            {{ __('Profile saved.') }}
        </div>
    @endif

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Basic info') }}</h2>
        <div class="mt-1 flex gap-4 text-xs text-ink-400">
            <span>{{ __('Slug') }}: <span class="font-mono">{{ $clinic->slug }}</span></span>
            <span>{{ __('City') }}: {{ $clinic->city?->name }}</span>
        </div>
        <p class="mt-1 text-xs text-ink-400">{{ __('Slug and city can only be changed by Clinicest — contact us if these need to change.') }}</p>

        <form wire:submit="save" class="mt-4 space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700">{{ __('Clinic name') }}</label>
                    <input type="text" wire:model="name" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @error('name') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700">{{ __('About') }}</label>
                    <textarea wire:model="about" rows="3" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                    @error('about') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Founded year') }}</label>
                    <input type="number" wire:model="founded_year" class="mt-1.5 w-full rounded-md border-ink-300 font-mono text-sm tabular-nums">
                    @error('founded_year') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Address') }}</label>
                    <input type="text" wire:model="address" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Phone') }}</label>
                    <input type="text" wire:model="phone" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('WhatsApp') }}</label>
                    <input type="text" wire:model="whatsapp" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Email') }}</label>
                    <input type="email" wire:model="email" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @error('email') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Website') }}</label>
                    <input type="text" wire:model="website" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @error('website') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Languages spoken') }}</label>
                <div class="mt-2 flex flex-wrap gap-4">
                    @foreach (['en' => 'English', 'de' => 'Deutsch', 'tr' => 'Türkçe'] as $code => $label)
                        <label class="flex items-center gap-2 text-sm text-ink-700">
                            <input type="checkbox" wire:model="languages" value="{{ $code }}" class="rounded border-ink-300">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <x-button type="submit" size="sm" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
            </x-button>
        </form>
    </div>

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Gallery') }}</h2>
        <p class="mt-1 text-xs text-ink-500">{{ __('Shown on your public profile. The starred photo is your cover photo. Use the arrows to reorder.') }}</p>

        <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($media as $item)
                <div wire:key="media-{{ $item->id }}" class="group relative aspect-square overflow-hidden rounded-md border border-ink-200">
                    <img src="{{ $item->thumb_url }}" alt="{{ $item->alt }}" class="h-full w-full object-cover">
                    @if ($item->is_cover)
                        <span class="absolute left-1.5 top-1.5 rounded-full bg-gold-500 px-1.5 py-0.5 text-[10px] font-semibold text-brand-950">★</span>
                    @endif
                    <div class="absolute inset-x-0 top-0 flex justify-end gap-1 p-1.5 opacity-0 transition group-hover:opacity-100">
                        <button type="button" wire:click="moveMedia({{ $item->id }}, -1)" @disabled($loop->first)
                                class="rounded bg-white/90 px-1.5 py-0.5 text-[11px] font-medium text-ink-800 hover:bg-white disabled:opacity-40" aria-label="{{ __('Move left') }}">←</button>
                        <button type="button" wire:click="moveMedia({{ $item->id }}, 1)" @disabled($loop->last)
                                class="rounded bg-white/90 px-1.5 py-0.5 text-[11px] font-medium text-ink-800 hover:bg-white disabled:opacity-40" aria-label="{{ __('Move right') }}">→</button>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 flex items-end justify-center gap-1.5 bg-black/50 p-2 opacity-0 transition group-hover:opacity-100">
                        @unless ($item->is_cover)
                            <button type="button" wire:click="setCoverMedia({{ $item->id }})" class="rounded bg-white/90 px-2 py-1 text-[11px] font-medium text-ink-800 hover:bg-white">
                                {{ __('Set cover') }}
                            </button>
                        @endunless
                        <button type="button" wire:click="deleteMedia({{ $item->id }})" wire:confirm="{{ __('Delete this photo?') }}"
                                class="rounded bg-white/90 px-2 py-1 text-[11px] font-medium text-danger-600 hover:bg-white">
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <form wire:submit="uploadMedia" class="mt-4 flex flex-wrap items-end gap-3 border-t border-ink-100 pt-4">
            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Add a photo') }}</label>
                <input type="file" wire:model="newMedia" accept="image/*" class="mt-1.5 text-sm">
                @error('newMedia') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                <div wire:loading wire:target="newMedia" class="mt-1 text-xs text-ink-400">{{ __('Uploading…') }}</div>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium text-ink-700">{{ __('Caption (optional)') }}</label>
                <input type="text" wire:model="newMediaCaption" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
            </div>
            <x-button type="submit" size="sm" wire:loading.attr="disabled" wire:target="uploadMedia">
                {{ __('Upload') }}
            </x-button>
        </form>
    </div>

    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Treatments & pricing') }}</h2>
        <ul class="mt-4 divide-y divide-ink-100">
            @forelse ($offeredTreatments as $treatment)
                <li class="py-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="flex-1 text-sm font-medium text-ink-900">{{ $treatment->getTranslation('name', app()->getLocale()) }}</span>
                        <div class="flex items-center gap-1">
                            <input type="text" wire:model="prices.{{ $treatment->id }}.currency" maxlength="3" class="w-16 rounded-md border-ink-300 text-xs uppercase">
                            <input type="number" step="0.01" min="0" wire:model="prices.{{ $treatment->id }}.min" class="w-24 rounded-md border-ink-300 text-sm">
                            <span class="text-ink-400">–</span>
                            <input type="number" step="0.01" min="0" wire:model="prices.{{ $treatment->id }}.max" class="w-24 rounded-md border-ink-300 text-sm">
                        </div>
                        <button type="button" wire:click="updateTreatmentPrice({{ $treatment->id }})" class="text-xs font-medium text-brand-700 hover:underline">
                            {{ __('Save') }}
                        </button>
                        <button type="button" wire:click="toggleTreatmentAvailability({{ $treatment->id }})"
                                @class(['text-xs font-semibold', 'text-success-600' => $treatment->pivot->is_available, 'text-ink-400' => ! $treatment->pivot->is_available])>
                            {{ $treatment->pivot->is_available ? __('Available') : __('Unavailable') }}
                        </button>
                        <button type="button" wire:click="removeTreatment({{ $treatment->id }})"
                                wire:confirm="{{ __('Remove this treatment from your profile?') }}"
                                class="text-xs font-medium text-danger-500 hover:underline">
                            {{ __('Remove') }}
                        </button>
                    </div>
                    @error("prices.{$treatment->id}.min") <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    @error("prices.{$treatment->id}.max") <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </li>
            @empty
                <li class="py-3 text-sm text-ink-500">{{ __('No treatments added yet.') }}</li>
            @endforelse
        </ul>

        @if ($availableTreatments->isNotEmpty())
            <div class="mt-4 border-t border-ink-100 pt-4">
                <p class="text-sm font-medium text-ink-700">{{ __('Add a treatment') }}</p>
                <div class="mt-2 flex flex-wrap items-end gap-2">
                    <select wire:model="newTreatmentId" class="rounded-md border-ink-300 text-sm">
                        <option value="">{{ __('Select a treatment') }}</option>
                        @foreach ($availableTreatments as $treatment)
                            <option value="{{ $treatment->id }}">{{ $treatment->getTranslation('name', app()->getLocale()) }}</option>
                        @endforeach
                    </select>
                    <input type="text" wire:model="newCurrency" placeholder="{{ __('Currency') }}" maxlength="3" class="w-16 rounded-md border-ink-300 text-sm uppercase">
                    <input type="number" step="0.01" min="0" wire:model="newPriceMin" placeholder="{{ __('Min') }}" class="w-24 rounded-md border-ink-300 text-sm">
                    <input type="number" step="0.01" min="0" wire:model="newPriceMax" placeholder="{{ __('Max') }}" class="w-24 rounded-md border-ink-300 text-sm">
                    <x-button type="button" wire:click="addTreatment" size="sm">{{ __('Add') }}</x-button>
                </div>
                @error('newTreatmentId') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                @error('newPriceMin') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                @error('newPriceMax') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>
</div>
