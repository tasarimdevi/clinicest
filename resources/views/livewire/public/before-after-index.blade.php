<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('Before & After')],
    ]" />

    <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('Before & After') }}</h1>
    <p class="mt-3 max-w-2xl text-ink-600">
        {{ __('Real cases from verified clinics, shown only with the patient\'s documented consent.') }}
    </p>

    <div class="mt-8">
        <select wire:model.live="treatment" class="rounded-md border-ink-300 text-sm">
            <option value="">{{ __('All treatments') }}</option>
            @foreach ($treatments as $t)
                <option value="{{ $t->id }}">{{ $t->getTranslation('name', app()->getLocale()) }}</option>
            @endforeach
        </select>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($cases as $case)
            <x-before-after-card :case="$case" />
        @empty
            <p class="col-span-full text-sm text-ink-500">{{ __('No cases match this filter yet.') }}</p>
        @endforelse
    </div>
</div>
