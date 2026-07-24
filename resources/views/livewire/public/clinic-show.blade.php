<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <x-breadcrumbs :items="[
        ['name' => __('nav.home'), 'url' => route('home')],
        ['name' => __('nav.clinics'), 'url' => route('clinics.index')],
        ['name' => $clinic->getTranslation('name', app()->getLocale())],
    ]" />

    <div class="grid grid-cols-1 gap-10 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="font-serif text-3xl font-medium text-ink-900">
                        {{ $clinic->getTranslation('name', app()->getLocale()) }}
                    </h1>
                    <p class="mt-1 text-ink-500">{{ $clinic->city?->name }}</p>
                </div>
                <x-verification-badge :tier="$clinic->verification_tier->value" />
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-ink-600">
                @if ($clinic->rating_count > 0)
                    <span class="font-mono tabular-nums">★ {{ number_format($clinic->rating_avg, 1) }} ({{ $clinic->rating_count }})</span>
                @endif
                @if ($clinic->response_time_minutes)
                    <span>{{ __('Responds within') }} {{ $clinic->response_time_minutes }} {{ __('min') }}</span>
                @endif
                @if (! empty($clinic->languages_json))
                    <span>{{ collect($clinic->languages_json)->map(fn ($l) => strtoupper($l))->implode(' · ') }}</span>
                @endif
            </div>

            @if ($about = $clinic->getTranslation('about', app()->getLocale()))
                <p class="mt-6 max-w-2xl text-ink-700">{{ $about }}</p>
            @endif

            <dl class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                @if ($clinic->founded_year)
                    <div><dt class="text-ink-400">{{ __('Founded') }}</dt><dd class="font-mono font-medium text-ink-900">{{ $clinic->founded_year }}</dd></div>
                @endif
                @if ($clinic->patients_treated)
                    <div><dt class="text-ink-400">{{ __('Patients treated') }}</dt><dd class="font-mono font-medium tabular-nums text-ink-900">{{ number_format($clinic->patients_treated) }}+</dd></div>
                @endif
            </dl>

            {{-- Treatments & prices --}}
            @if ($treatments->isNotEmpty())
                <div class="mt-10">
                    <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Treatments & prices') }}</h2>
                    <div class="mt-4 overflow-x-auto rounded-lg border border-ink-200 bg-white shadow-card">
                        <table class="min-w-full divide-y divide-ink-100 text-sm">
                            <tbody class="divide-y divide-ink-100">
                                @foreach ($treatments as $treatment)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('treatments.show', $treatment->slug) }}" class="font-medium text-ink-900 hover:text-brand-600">
                                                {{ $treatment->getTranslation('name', app()->getLocale()) }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono tabular-nums text-teal-600">
                                            @if ($treatment->pivot->price_min)
                                                {{ $treatment->pivot->currency }} {{ number_format($treatment->pivot->price_min / 100, 0) }}@if($treatment->pivot->price_max)–{{ number_format($treatment->pivot->price_max / 100, 0) }}@endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Doctors --}}
            @if ($doctors->isNotEmpty())
                <div class="mt-10">
                    <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Doctors') }}</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($doctors as $doctor)
                            <x-doctor-card :doctor="$doctor" />
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Contact --}}
            <div class="mt-10">
                <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Location & contact') }}</h2>
                <dl class="mt-4 space-y-2 text-sm text-ink-600">
                    @if ($clinic->address)
                        <div>{{ $clinic->address }}</div>
                    @endif
                    @if ($clinic->phone)
                        <div>{{ __('Phone') }}: {{ $clinic->phone }}</div>
                    @endif
                    @if ($clinic->website)
                        <div><a href="{{ $clinic->website }}" class="text-brand-600 hover:underline" target="_blank" rel="noopener">{{ $clinic->website }}</a></div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Lead card --}}
        <div>
            <div class="sticky top-24 rounded-lg border border-ink-200 bg-white p-6 shadow-card">
                <h2 class="font-serif text-lg font-medium text-ink-900">
                    {{ __('Request a free plan from') }} {{ $clinic->getTranslation('name', app()->getLocale()) }}
                </h2>
                <p class="mt-2 text-sm text-ink-500">{{ __('Free · No obligation · Reply within 24h') }}</p>
                <x-button :href="route('get-quote', ['clinic' => $clinic->id])" as="a" class="mt-4 w-full">
                    {{ __('home.hero_cta') }}
                </x-button>
                @if ($clinic->whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $clinic->whatsapp) }}" target="_blank" rel="noopener"
                       class="mt-3 flex w-full items-center justify-center gap-2 rounded-md border border-ink-200 py-2.5 text-sm font-semibold text-ink-700 hover:bg-ink-50">
                        {{ __('WhatsApp') }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if ($relatedClinics->isNotEmpty())
        <div class="mt-14">
            <h2 class="font-serif text-xl font-medium text-ink-900">{{ __('Other clinics nearby') }}</h2>
            <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-3">
                @foreach ($relatedClinics as $rel)
                    <x-clinic-card :clinic="$rel" />
                @endforeach
            </div>
        </div>
    @endif

    <script type="application/ld+json">{!! json_encode(app(\App\Services\SchemaService::class)->medicalClinic($clinic), JSON_UNESCAPED_SLASHES) !!}</script>
</div>
