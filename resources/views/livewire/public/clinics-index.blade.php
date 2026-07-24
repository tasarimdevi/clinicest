<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('nav.clinics')],
    ]" />

    <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">
        {{ __('Verified Dental Clinics in Turkey') }}
    </h1>
    <p class="mt-3 max-w-2xl text-ink-600">
        {{ __('Every clinic here has passed our verification standard — license check, sterilization audit, and named dentist credentials.') }}
    </p>

    <div class="mt-8 flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Search by name…') }}"
               class="w-full max-w-xs rounded-md border-ink-300 text-sm">
        <select wire:model.live="treatment" class="rounded-md border-ink-300 text-sm">
            <option value="">{{ __('All treatments') }}</option>
            @foreach ($treatments as $t)
                <option value="{{ $t->id }}">{{ $t->getTranslation('name', app()->getLocale()) }}</option>
            @endforeach
        </select>
        <select wire:model.live="city" class="rounded-md border-ink-300 text-sm">
            <option value="">{{ __('All cities') }}</option>
            @foreach ($cities as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="tier" class="rounded-md border-ink-300 text-sm">
            <option value="">{{ __('All verification tiers') }}</option>
            @foreach ($tiers as $t)
                <option value="{{ $t->value }}">{{ $t->label() }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($clinics as $clinic)
            <x-clinic-card :clinic="$clinic" />
        @empty
            <p class="col-span-full text-sm text-ink-500">{{ __('No clinics match these filters.') }}</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $clinics->links() }}
    </div>
</div>
