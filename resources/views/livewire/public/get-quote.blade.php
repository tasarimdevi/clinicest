<div class="mx-auto max-w-xl px-4 py-16 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink-900">{{ __('Get Your Free Treatment Plan') }}</h1>
    <p class="mt-2 text-sm text-ink-500">
        {{ __('Free · No obligation · 100% confidential · Reply within 24h') }}
    </p>

    @if ($submitted)
        <div class="mt-8 rounded-lg border border-success-500/30 bg-success-500/5 p-6">
            <p class="font-semibold text-success-600">{{ __("Thank you — we've received your request.") }}</p>
            <p class="mt-2 text-sm text-ink-600">
                {{ __('Our team will match you with verified clinics and follow up within 24 hours.') }}
            </p>
        </div>
    @else
        <form wire:submit="submit" class="mt-8 space-y-5">
            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('nav.treatments') }}</label>
                <select wire:model="primary_treatment_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    <option value="">{{ __('Select a treatment (optional)') }}</option>
                    @foreach ($treatments as $treatment)
                        <option value="{{ $treatment->id }}" @selected($primary_treatment_id === $treatment->id)>{{ $treatment->getTranslation('name', app()->getLocale()) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Country') }}</label>
                <select wire:model="country_id" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                    <option value="">{{ __('Select your country (optional)') }}</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected($country_id === $country->id)>{{ $country->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Full name') }}</label>
                <input type="text" wire:model="full_name" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                @error('full_name') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Email') }}</label>
                <input type="email" wire:model="email" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
                @error('email') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('WhatsApp (optional)') }}</label>
                <input type="text" wire:model="whatsapp" class="mt-1.5 w-full rounded-md border-ink-300 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-ink-700">{{ __('Tell us about your case (optional)') }}</label>
                <textarea wire:model="message" rows="4" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
            </div>

            <label class="flex items-start gap-2 text-sm text-ink-600">
                <input type="checkbox" wire:model="consent" class="mt-0.5 rounded border-ink-300">
                {{ __('I consent to Clinicest processing my data to match me with clinics, per the') }}
                <a href="{{ route('legal.privacy') }}" class="text-brand-700 underline">{{ __('Privacy Policy') }}</a>.
            </label>
            @error('consent') <p class="text-xs text-danger-500">{{ $message }}</p> @enderror

            <x-button type="submit" size="lg" class="w-full" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">{{ __('home.hero_cta') }}</span>
                <span wire:loading wire:target="submit">{{ __('Sending…') }}</span>
            </x-button>
        </form>
    @endif
</div>
