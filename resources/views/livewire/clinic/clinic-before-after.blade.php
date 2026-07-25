<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-lg font-semibold text-ink-900">{{ __('Before / After cases') }}</h1>
        <p class="mt-1 text-sm text-ink-500">
            {{ __('Real patient results only. Every case is reviewed by Clinicest before it appears on your public profile.') }}
        </p>
    </div>

    {{-- Existing cases --}}
    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Your cases') }}</h2>
        <div class="mt-4 space-y-3">
            @forelse ($cases as $case)
                <div class="flex items-center gap-4 rounded-md border border-ink-100 p-3">
                    <div class="flex shrink-0 gap-1">
                        <img src="{{ $case->before_url }}" alt="{{ __('Before') }}" class="h-14 w-14 rounded object-cover">
                        <img src="{{ $case->after_url }}" alt="{{ __('After') }}" class="h-14 w-14 rounded object-cover">
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-ink-900">{{ $case->getTranslation('title', 'en') }}</p>
                        <p class="text-xs text-ink-500">{{ $case->treatment?->getTranslation('name', app()->getLocale()) }}</p>
                    </div>
                    @if ($case->is_published)
                        <span class="inline-flex rounded-full bg-success-500/10 px-2.5 py-1 text-xs font-medium text-success-600">{{ __('Published') }}</span>
                    @else
                        <span class="inline-flex rounded-full bg-gold-500/10 px-2.5 py-1 text-xs font-medium text-gold-600">{{ __('Under review') }}</span>
                    @endif
                    <button type="button" wire:click="delete({{ $case->id }})" wire:confirm="{{ __('Delete this case?') }}"
                            class="text-xs font-medium text-danger-500 hover:underline">
                        {{ __('Delete') }}
                    </button>
                </div>
            @empty
                <p class="text-sm text-ink-500">{{ __('No cases yet.') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Upload new --}}
    <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
        <h2 class="text-sm font-semibold text-ink-900">{{ __('Submit a new case') }}</h2>

        <form wire:submit="submit" class="mt-4 space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Before photo') }}</label>
                    <input type="file" wire:model="before" accept="image/*" class="mt-1.5 text-sm">
                    @error('before') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="before" class="mt-1 text-xs text-ink-400">{{ __('Uploading…') }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('After photo') }}</label>
                    <input type="file" wire:model="after" accept="image/*" class="mt-1.5 text-sm">
                    @error('after') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="after" class="mt-1 text-xs text-ink-400">{{ __('Uploading…') }}</div>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700">{{ __('Title') }}</label>
                    <input type="text" wire:model="title" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @error('title') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Treatment') }}</label>
                    <select wire:model="treatment_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        <option value="">{{ __('Select a treatment') }}</option>
                        @foreach ($treatments as $treatment)
                            <option value="{{ $treatment->id }}">{{ $treatment->getTranslation('name', app()->getLocale()) }}</option>
                        @endforeach
                    </select>
                    @error('treatment_id') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Doctor (optional)') }}</label>
                    <select wire:model="doctor_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        <option value="">{{ __('Select a doctor') }}</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Patient country (optional)') }}</label>
                    <select wire:model="patient_country_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        <option value="">{{ __('Select a country') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700">{{ __('Description (optional)') }}</label>
                    <textarea wire:model="description" rows="2" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                </div>
            </div>

            <label class="flex items-start gap-2 text-sm text-ink-700">
                <input type="checkbox" wire:model="consent" class="mt-0.5 rounded border-ink-300">
                {{ __('I confirm this patient gave documented consent to publish these photos, and that they are genuine, unedited results.') }}
            </label>
            @error('consent') <p class="text-xs text-danger-500">{{ $message }}</p> @enderror

            <x-button type="submit" size="sm" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">{{ __('Submit for review') }}</span>
                <span wire:loading wire:target="submit">{{ __('Submitting…') }}</span>
            </x-button>
        </form>
    </div>
</div>
