<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? '' }}">

    @stack('meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-ink-50 text-ink-900 antialiased">

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-md focus:bg-brand-600 focus:px-4 focus:py-2 focus:text-white">
        {{ __('Skip to content') }}
    </a>

    {{-- The header/hero band is a deliberate always-dark zone (brand-950),
         independent of light/dark theme — see docs/03-design-system.md §1. --}}
    <header x-data="{ mobileOpen: false }"
        class="sticky top-0 z-40 border-b border-white/10 bg-brand-950/90 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="font-serif text-xl font-medium tracking-tight text-ink-50">
                Clin<span class="text-gold-400">i</span>cest
            </a>

            <nav class="hidden items-center gap-8 lg:flex">
                <a href="{{ route('treatments.index') }}" class="text-sm font-medium text-ink-300 hover:text-ink-50">{{ __('nav.treatments') }}</a>
                <a href="{{ route('clinics.index') }}" class="text-sm font-medium text-ink-300 hover:text-ink-50">{{ __('nav.clinics') }}</a>
                <a href="{{ route('doctors.index') }}" class="text-sm font-medium text-ink-300 hover:text-ink-50">{{ __('nav.doctors') }}</a>
                <a href="{{ route('reviews.index') }}" class="text-sm font-medium text-ink-300 hover:text-ink-50">{{ __('nav.reviews') }}</a>
            </nav>

            <div class="flex items-center gap-3">
                <div class="hidden items-center gap-1 font-mono text-xs font-medium text-ink-400 lg:flex" role="group" aria-label="{{ __('Language') }}">
                    <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'text-gold-400' : 'hover:text-ink-100' }}">EN</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ route('locale.switch', 'tr') }}" class="{{ app()->getLocale() === 'tr' ? 'text-gold-400' : 'hover:text-ink-100' }}">TR</a>
                </div>
                <a href="{{ route('get-quote') }}"
                   class="hidden rounded-md bg-gold-500 px-4 py-2 text-sm font-semibold text-brand-950 shadow-card transition hover:bg-gold-400 sm:inline-flex">
                    {{ __('nav.get_quote') }}
                </a>
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden" aria-label="{{ __('Menu') }}">
                    <svg class="h-6 w-6 text-ink-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>

        <nav x-show="mobileOpen" x-cloak class="border-t border-white/10 bg-brand-950 px-4 py-4 lg:hidden">
            <a href="{{ route('treatments.index') }}" class="block py-2 text-sm font-medium text-ink-200">{{ __('nav.treatments') }}</a>
            <a href="{{ route('clinics.index') }}" class="block py-2 text-sm font-medium text-ink-200">{{ __('nav.clinics') }}</a>
            <a href="{{ route('doctors.index') }}" class="block py-2 text-sm font-medium text-ink-200">{{ __('nav.doctors') }}</a>
            <a href="{{ route('reviews.index') }}" class="block py-2 text-sm font-medium text-ink-200">{{ __('nav.reviews') }}</a>
            <div class="mt-3 flex items-center gap-2 border-t border-white/10 pt-3 font-mono text-xs font-medium text-ink-400">
                <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'text-gold-400' : '' }}">EN</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('locale.switch', 'tr') }}" class="{{ app()->getLocale() === 'tr' ? 'text-gold-400' : '' }}">TR</a>
            </div>
        </nav>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="border-t border-ink-100 bg-ink-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-5">
                <div>
                    <h3 class="text-sm font-semibold text-ink-900">{{ __('nav.treatments') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li><a href="{{ route('treatments.index') }}" class="hover:text-brand-700">{{ __('nav.treatments') }}</a></li>
                        <li><a href="{{ route('clinics.index') }}" class="hover:text-brand-700">{{ __('nav.clinics') }}</a></li>
                    </ul>
                </div>
                @if (($footerCountries ?? collect())->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-ink-900">{{ __('Popular routes') }}</h3>
                        <ul class="mt-4 space-y-2 text-sm text-ink-600">
                            @foreach ($footerCountries as $fc)
                                <li><a href="{{ route('countries.show', $fc->slug) }}" class="hover:text-brand-700">{{ __('Turkey for :country', ['country' => $fc->name]) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div>
                    <h3 class="text-sm font-semibold text-ink-900">{{ __('Company') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li><a href="{{ route('about') }}" class="hover:text-brand-700">{{ __('nav.about') }}</a></li>
                        <li><a href="{{ route('how-it-works') }}" class="hover:text-brand-700">{{ __('nav.how_it_works') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-brand-700">{{ __('nav.contact') }}</a></li>
                        <li><a href="{{ route('blog.index') }}" class="hover:text-brand-700">{{ __('Blog') }}</a></li>
                        @guest
                            <li><a href="{{ route('for-clinics') }}" class="hover:text-brand-700">{{ __('List your clinic') }}</a></li>
                        @endguest
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink-900">{{ __('Trust') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li><a href="{{ route('faq') }}" class="hover:text-brand-700">{{ __('nav.faq') }}</a></li>
                        <li><a href="{{ route('cost-estimator') }}" class="hover:text-brand-700">{{ __('AI Cost Estimator') }}</a></li>
                        <li><a href="{{ route('guide.index') }}" class="hover:text-brand-700">{{ __('nav.guide') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink-900">{{ __('Legal') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li><a href="{{ route('legal.privacy') }}" class="hover:text-brand-700">{{ __('Privacy') }}</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="hover:text-brand-700">{{ __('Terms') }}</a></li>
                        <li><a href="{{ route('legal.gdpr') }}" class="hover:text-brand-700">GDPR</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 border-t border-ink-200 pt-6 text-xs text-ink-500">
                &copy; {{ date('Y') }} Clinicest. {{ __('All rights reserved.') }}
            </div>
        </div>
    </footer>

    <a href="https://wa.me/" target="_blank" rel="noopener"
       class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-success-500 text-white shadow-raised transition hover:scale-105"
       aria-label="WhatsApp">
        <svg class="h-7 w-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.29-1.39a9.9 9.9 0 0 0 4.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91C21.96 6.45 17.5 2 12.04 2z"/></svg>
    </a>

    <div class="fixed inset-x-0 bottom-0 z-30 flex gap-2 border-t border-ink-200 bg-white p-3 shadow-raised sm:hidden">
        <a href="{{ route('get-quote') }}" class="flex-1 rounded-md bg-brand-600 py-2.5 text-center text-sm font-semibold text-white">
            {{ __('nav.get_quote') }}
        </a>
    </div>

    @livewireScripts
</body>
</html>
