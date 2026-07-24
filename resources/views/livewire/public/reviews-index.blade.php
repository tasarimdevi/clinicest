<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('nav.reviews')],
    ]" />

    <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('Patient Reviews') }}</h1>
    <p class="mt-3 max-w-2xl text-ink-600">
        {{ __('Every review here is tied to a real Clinicest patient. Verified reviews are confirmed against a completed treatment.') }}
    </p>

    <div class="mt-8 flex flex-wrap gap-3">
        <select wire:model.live="clinic" class="rounded-md border-ink-300 text-sm">
            <option value="">{{ __('All clinics') }}</option>
            @foreach ($clinics as $c)
                <option value="{{ $c->id }}">{{ $c->getTranslation('name', app()->getLocale()) }}</option>
            @endforeach
        </select>
        <select wire:model.live="treatment" class="rounded-md border-ink-300 text-sm">
            <option value="">{{ __('All treatments') }}</option>
            @foreach ($treatments as $t)
                <option value="{{ $t->id }}">{{ $t->getTranslation('name', app()->getLocale()) }}</option>
            @endforeach
        </select>
        <select wire:model.live="rating" class="rounded-md border-ink-300 text-sm">
            <option value="">{{ __('All ratings') }}</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">★ {{ $i }}</option>
            @endfor
        </select>
    </div>

    <div class="mt-8 space-y-4">
        @forelse ($reviews as $review)
            <x-review-card :review="$review" />
        @empty
            <p class="text-sm text-ink-500">{{ __('No reviews match these filters.') }}</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $reviews->links() }}
    </div>
</div>
