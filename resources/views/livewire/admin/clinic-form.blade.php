<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        @if (! $clinic || auth()->user()->can('clinics.manage'))
        <form wire:submit="save" class="space-y-6">
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Basic info') }}</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Name') }}</label>
                        <input type="text" wire:model="name" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('name') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Slug') }}</label>
                        <input type="text" wire:model="slug" class="mt-1.5 w-full rounded-md border-ink-300 font-mono text-sm">
                        @error('slug') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('About') }}</label>
                        <textarea wire:model="about" rows="3" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Founded year') }}</label>
                        <input type="number" wire:model="founded_year" class="mt-1.5 w-full rounded-md border-ink-300 font-mono text-sm tabular-nums">
                        @error('founded_year') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Location & contact') }}</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('City') }}</label>
                        <select wire:model="city_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                            <option value="">{{ __('Select a city') }}</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                            @endforeach
                        </select>
                        @error('city_id') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
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
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Status') }}</h2>
                <div class="mt-3 flex flex-col gap-3">
                    <label class="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" wire:model="is_active" class="rounded border-ink-300">
                        {{ __('Active (visible to patients)') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm text-ink-700">
                        <input type="checkbox" wire:model="is_featured" class="rounded border-ink-300">
                        {{ __('Featured on homepage') }}
                    </label>
                </div>
            </div>

            <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
                {{ $clinic ? __('Save changes') : __('Create clinic') }}
            </x-button>
        </form>
        @else
        <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
            <h2 class="font-serif text-lg font-medium text-ink-900">{{ $clinic->getTranslation('name', 'en') }}</h2>
            <p class="mt-1 text-sm text-ink-500">{{ $clinic->city?->name }} &middot; {{ $clinic->address }}</p>
            <p class="mt-4 text-xs text-ink-400">
                {{ __('You have clinics.verify but not clinics.manage — you can review this profile and update its verification tier, but not edit its details.') }}
            </p>
        </div>
        @endif
    </div>

    <div class="space-y-6">
        @if ($clinic)
            @can('verify', $clinic)
                <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                    <h2 class="text-sm font-semibold text-ink-900">{{ __('Verification') }}</h2>
                    <p class="mt-1 text-xs text-ink-500">{{ __('Changing this sets verified_at and verified_by to you.') }}</p>
                    <div class="mt-4 flex flex-col gap-2">
                        @foreach ($tiers as $t)
                            <button wire:click="updateVerificationTier('{{ $t->value }}')"
                                    @class([
                                        'rounded-md px-3 py-2 text-left text-sm font-medium',
                                        'bg-brand-600 text-white' => $verification_tier === $t->value,
                                        'bg-ink-100 text-ink-600 hover:bg-ink-200' => $verification_tier !== $t->value,
                                    ])>
                                {{ $t->label() }}
                            </button>
                        @endforeach
                    </div>
                    @if ($clinic->verified_at)
                        <p class="mt-4 text-xs text-ink-500">
                            {{ __('Last verified') }} {{ $clinic->verified_at->format('d M Y H:i') }}
                        </p>
                    @endif
                </div>
            @endcan
        @else
            <div class="rounded-lg border border-dashed border-ink-300 bg-ink-50 p-6 text-sm text-ink-500">
                {{ __('Save the clinic first to unlock the verification panel.') }}
            </div>
        @endif
    </div>
</div>
