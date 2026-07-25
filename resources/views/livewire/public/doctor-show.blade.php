<div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('nav.doctors'), 'url' => route('doctors.index')],
        ['name' => $doctor->full_name],
    ]" />

    <div class="flex items-start gap-5">
        @if ($doctor->photo_url)
            <img src="{{ $doctor->photo_url }}" alt="{{ $doctor->full_name }}" class="h-24 w-24 shrink-0 rounded-full object-cover shadow-card">
        @endif
        <div>
            <h1 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ $doctor->full_name }}</h1>

            <p class="mt-2 text-lg text-ink-600">
                @if ($title = $doctor->getTranslation('title', app()->getLocale())) {{ $title }} @endif
                @if ($specialty = $doctor->getTranslation('specialty', app()->getLocale())) &middot; {{ $specialty }} @endif
            </p>
        </div>
    </div>

    @if ($doctor->clinic)
        <p class="mt-1 text-ink-500">
            {{ __('at') }}
            <a href="{{ route('clinics.show', $doctor->clinic->slug) }}" class="text-brand-600 hover:underline">
                {{ $doctor->clinic->getTranslation('name', app()->getLocale()) }}
            </a>
        </p>
    @endif

    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-ink-600">
        @if ($doctor->years_experience)
            <span class="font-mono tabular-nums">{{ $doctor->years_experience }} {{ __('years experience') }}</span>
        @endif
        @if ($doctor->rating_count > 0)
            <span class="font-mono tabular-nums">★ {{ number_format($doctor->rating_avg, 1) }} ({{ $doctor->rating_count }})</span>
        @endif
        @if (! empty($doctor->languages_json))
            <span>{{ collect($doctor->languages_json)->map(fn ($l) => strtoupper($l))->implode(' · ') }}</span>
        @endif
    </div>

    <x-button :href="route('get-quote', $doctor->clinic_id ? ['clinic' => $doctor->clinic_id] : [])" as="a" size="lg" class="mt-6">
        {{ __('Request a consultation') }}
    </x-button>

    @if ($bio = $doctor->getTranslation('bio', app()->getLocale()))
        <div class="prose mt-10 max-w-none text-ink-700">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('About') }}</h2>
            <p class="mt-3">{{ $bio }}</p>
        </div>
    @endif

    @if ($reviews->isNotEmpty())
        <div class="mt-10">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Patient Reviews') }}</h2>
            <div class="mt-4 space-y-4">
                @foreach ($reviews as $review)
                    <x-review-card :review="$review" />
                @endforeach
            </div>
        </div>
    @endif

    @if ($related->isNotEmpty())
        <div class="mt-14">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Other dentists at this clinic') }}</h2>
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ($related as $rel)
                    <x-doctor-card :doctor="$rel" />
                @endforeach
            </div>
        </div>
    @endif
</div>
