<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('nav.doctors')],
    ]" />

    <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">
        {{ __('Dentists at our verified clinics') }}
    </h1>
    <p class="mt-3 max-w-2xl text-ink-600">
        {{ __('Every dentist listed here is credentialed at a Clinicest-verified clinic.') }}
    </p>

    <div class="mt-8 flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ __('Search by name…') }}"
               class="w-full max-w-xs rounded-md border-ink-300 text-sm">
        <select wire:model.live="clinic" class="rounded-md border-ink-300 text-sm">
            <option value="">{{ __('All clinics') }}</option>
            @foreach ($clinics as $c)
                <option value="{{ $c->id }}">{{ $c->getTranslation('name', app()->getLocale()) }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($doctors as $doctor)
            <x-doctor-card :doctor="$doctor" />
        @empty
            <p class="col-span-full text-sm text-ink-500">{{ __('No doctors match these filters.') }}</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $doctors->links() }}
    </div>
</div>
