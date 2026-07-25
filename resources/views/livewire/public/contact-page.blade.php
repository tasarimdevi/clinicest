<div>
    <div class="mx-auto max-w-4xl px-4 pt-12 sm:px-6 lg:px-8">
        <x-breadcrumbs :items="[
            ['name' => __('nav.home'), 'url' => route('home')],
            ['name' => __('nav.contact')],
        ]" />

        <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('nav.contact') }}</h1>
        <p class="mt-4 max-w-2xl text-lg text-ink-600">
            {{ __("Have a question before you get a quote? Reach us directly — we reply within 24 hours.") }}
        </p>
    </div>

    <div class="mx-auto grid max-w-4xl grid-cols-1 gap-10 px-4 py-10 sm:px-6 lg:grid-cols-5 lg:px-8">
        <div class="lg:col-span-2">
            <div class="space-y-6 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <div>
                    <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Email') }}</p>
                    <a href="mailto:{{ config('clinicest.contact_email') }}" class="mt-1 block text-sm font-medium text-brand-700 hover:underline">
                        {{ config('clinicest.contact_email') }}
                    </a>
                </div>
                <div>
                    <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('WhatsApp') }}</p>
                    <a href="https://wa.me/" target="_blank" rel="noopener" class="mt-1 block text-sm font-medium text-brand-700 hover:underline">
                        {{ __('Message us on WhatsApp') }}
                    </a>
                </div>
                <div>
                    <p class="font-mono text-xs uppercase tracking-wide text-ink-400">{{ __('Response time') }}</p>
                    <p class="mt-1 text-sm text-ink-700">{{ __('Within 24 hours, every day.') }}</p>
                </div>
                <div>
                    <a href="{{ route('faq') }}" class="text-sm font-medium text-brand-700 hover:underline">
                        {{ __('Looking for a quick answer? Check our FAQ →') }}
                    </a>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            @if ($sent)
                <div class="rounded-lg border border-success-500/30 bg-success-500/5 p-6">
                    <p class="font-semibold text-success-600">{{ __("Thank you — your message has been sent.") }}</p>
                    <p class="mt-2 text-sm text-ink-600">{{ __("We'll reply within 24 hours.") }}</p>
                </div>
            @else
                <form wire:submit="submit" class="space-y-5">
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
                        <label class="block text-sm font-medium text-ink-700">{{ __('Message') }}</label>
                        <textarea wire:model="message" rows="6" class="mt-1.5 w-full rounded-md border-ink-300 text-sm"></textarea>
                        @error('message') <p class="mt-1 text-xs text-danger-500">{{ $message }}</p> @enderror
                    </div>
                    <x-button type="submit" size="lg" wire:loading.attr="disabled" wire:target="submit">
                        <span wire:loading.remove wire:target="submit">{{ __('Send message') }}</span>
                        <span wire:loading wire:target="submit">{{ __('Sending…') }}</span>
                    </x-button>
                </form>
            @endif
        </div>
    </div>
</div>
