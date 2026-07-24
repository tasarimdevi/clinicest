<div class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-ink-900">{{ $title }}</h1>
    <p class="mt-4 text-ink-600">
        {{ __('This page is scaffolded and ready for content — see docs/04-wireframes.md for the full spec.') }}
    </p>
    <x-button :href="route('get-quote')" as="a" class="mt-8">{{ __('nav.get_quote') }}</x-button>
</div>
