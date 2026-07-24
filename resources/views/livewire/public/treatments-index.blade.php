<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('nav.treatments')],
    ]" />

    <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">
        {{ __('home.treatments_eyebrow') }}
    </p>
    <h1 class="mt-2 font-serif text-3xl font-medium text-ink-900 sm:text-4xl">
        {{ __('Dental Treatments in Turkey — transparent prices, verified clinics') }}
    </h1>
    <p class="mt-4 max-w-2xl text-ink-600">
        {{ __('Every listing on Clinicest shows a real starting price from our verified clinic network — no quote-on-request. Tell us your case and we confirm the exact plan for free.') }}
    </p>

    <div class="mt-8 flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Search treatments…') }}"
               class="w-full max-w-xs rounded-md border-ink-300 text-sm">
        <div class="flex flex-wrap gap-2">
            <button wire:click="$set('category', '')"
                    @class(['rounded-full px-3 py-1.5 text-xs font-semibold', 'bg-brand-600 text-white' => $category === '', 'bg-ink-100 text-ink-600 hover:bg-ink-200' => $category !== ''])>
                {{ __('All') }}
            </button>
            @foreach ($categories as $cat)
                <button wire:click="$set('category', '{{ $cat->id }}')"
                        @class(['rounded-full px-3 py-1.5 text-xs font-semibold', 'bg-brand-600 text-white' => (string) $category === (string) $cat->id, 'bg-ink-100 text-ink-600 hover:bg-ink-200' => (string) $category !== (string) $cat->id])>
                    {{ $cat->getTranslation('name', app()->getLocale()) }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-px overflow-hidden rounded-lg border border-ink-200 bg-ink-200 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($treatments as $treatment)
            <x-treatment-card :treatment="$treatment" />
        @empty
            <p class="col-span-full bg-white p-6 text-sm text-ink-500">{{ __('No treatments match these filters.') }}</p>
        @endforelse
    </div>

    <div class="mt-14 rounded-2xl bg-brand-950 px-8 py-12 text-center text-ink-50">
        <h2 class="font-serif text-2xl font-medium">{{ __('Not sure which treatment you need?') }}</h2>
        <p class="mt-2 text-ink-300">{{ __('Tell us about your case and get a free, no-obligation treatment plan.') }}</p>
        <x-button :href="route('get-quote')" as="a" variant="gold" size="lg" class="mt-6">
            {{ __('home.hero_cta') }}
        </x-button>
    </div>
</div>
