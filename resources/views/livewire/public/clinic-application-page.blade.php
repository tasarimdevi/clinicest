<div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('List your clinic')],
    ]" />

    <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('List your clinic on Clinicest') }}</h1>
    <p class="mt-4 text-lg text-ink-600">
        {{ __('Apply below — our team reviews every application against our verification standard before a clinic goes live. Free to apply, no upfront cost.') }}
    </p>

    @if ($submitted)
        <div class="mt-8 rounded-lg border border-success-500/30 bg-success-500/5 p-6">
            <p class="font-semibold text-success-600">{{ __('Application received.') }}</p>
            <p class="mt-2 text-sm text-ink-600">{{ __("We'll review your details and reply within a few days.") }}</p>
        </div>
    @else
        <form wire:submit="submit" class="mt-8 space-y-8">
            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('About your clinic') }}</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Clinic name') }}</label>
                        <input type="text" wire:model="clinic_name" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('clinic_name') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
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
                        <label class="block text-sm font-medium text-ink-700">{{ __('Website') }}</label>
                        <input type="text" wire:model="website" placeholder="https://" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('website') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Clinic phone') }}</label>
                        <input type="text" wire:model="phone" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Clinic WhatsApp') }}</label>
                        <input type="text" wire:model="whatsapp" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('Clinic email') }}</label>
                        <input type="email" wire:model="email" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('email') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-ink-700">{{ __('About the clinic') }}</label>
                        <textarea wire:model="about" rows="3" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Verification') }}</h2>
                <p class="mt-1 text-xs text-ink-500">{{ __('Link to your practice license, credentials, or ISO certification — this speeds up review.') }}</p>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-ink-700">{{ __('Credentials link (optional)') }}</label>
                    <input type="text" wire:model="credentials_url" placeholder="https://" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    @error('credentials_url') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-ink-700">{{ __('Anything else we should know? (optional)') }}</label>
                    <textarea wire:model="application_message" rows="3" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                </div>
            </div>

            <div class="rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="text-sm font-semibold text-ink-900">{{ __('Your account') }}</h2>
                <p class="mt-1 text-xs text-ink-500">{{ __("You'll use this to log in and manage your clinic's leads once approved.") }}</p>
                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Your name') }}</label>
                        <input type="text" wire:model="owner_name" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('owner_name') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Your email') }}</label>
                        <input type="email" wire:model="owner_email" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('owner_email') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Password') }}</label>
                        <input type="password" wire:model="password" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                        @error('password') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-ink-700">{{ __('Confirm password') }}</label>
                        <input type="password" wire:model="password_confirmation" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    </div>
                </div>
            </div>

            <x-button type="submit" size="lg" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">{{ __('Submit application') }}</span>
                <span wire:loading wire:target="submit">{{ __('Submitting…') }}</span>
            </x-button>
        </form>
    @endif
</div>
