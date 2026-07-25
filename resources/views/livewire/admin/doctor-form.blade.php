<div class="max-w-2xl">
    <form wire:submit="save" class="space-y-6">
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('Profile') }}</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Full name') }}</label>
                    <input type="text" wire:model="full_name" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @error('full_name') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Slug') }}</label>
                    <input type="text" wire:model="slug" class="mt-1.5 w-full rounded-md border-ink-300 font-mono text-sm">
                    @error('slug') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Clinic') }}</label>
                    <select wire:model="clinic_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        <option value="">{{ __('Select a clinic') }}</option>
                        @foreach ($clinics as $clinic)
                            <option value="{{ $clinic->id }}">{{ $clinic->getTranslation('name', 'en') }}</option>
                        @endforeach
                    </select>
                    @error('clinic_id') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Title') }}</label>
                    <input type="text" wire:model="title" placeholder="e.g. DDS" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Specialty') }}</label>
                    <input type="text" wire:model="specialty" placeholder="e.g. Prosthodontics" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-ink-700">{{ __('Years of experience') }}</label>
                    <input type="number" wire:model="years_experience" class="mt-1.5 w-full rounded-md border-ink-300 font-mono text-sm tabular-nums">
                    @error('years_experience') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700">{{ __('Bio') }}</label>
                    <textarea wire:model="bio" rows="3" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-ink-700">{{ __('Profile photo') }}</label>
                    <div class="mt-2 flex items-center gap-4">
                        @if ($doctor?->photo_url)
                            <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->full_name }}" class="h-16 w-16 rounded-full object-cover">
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-ink-100 text-lg font-semibold text-ink-400">
                                {{ Str::of($full_name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') ?: '—' }}
                            </div>
                        @endif
                        <div>
                            <input type="file" wire:model="photo" accept="image/*" class="text-sm">
                            @error('photo') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                            <div wire:loading wire:target="photo" class="mt-1 text-xs text-ink-400">{{ __('Uploading…') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="text-sm font-semibold text-ink-900">{{ __('Languages spoken') }}</h2>
            <div class="mt-3 flex flex-wrap gap-4">
                @foreach (['en' => 'English', 'de' => 'Deutsch', 'tr' => 'Türkçe'] as $code => $label)
                    <label class="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" wire:model="languages" value="{{ $code }}" class="rounded border-ink-300">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <label class="mt-4 flex items-center gap-2 text-sm text-ink-700">
                <input type="checkbox" wire:model="is_featured" class="rounded border-ink-300">
                {{ __('Featured on homepage') }}
            </label>
        </div>

        <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
            {{ $doctor ? __('Save changes') : __('Create doctor') }}
        </x-button>
    </form>
</div>
