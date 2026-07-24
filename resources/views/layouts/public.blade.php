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
<body class="bg-white text-ink-800 antialiased">

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:rounded-md focus:bg-brand-700 focus:px-4 focus:py-2 focus:text-white">
        {{ __('Skip to content') }}
    </a>

    <header x-data="{ scrolled: false, mobileOpen: false }" @scroll.window="scrolled = window.scrollY > 8"
        :class="scrolled ? 'bg-white/80 backdrop-blur-xl shadow-card' : 'bg-white'"
        class="sticky top-0 z-40 border-b border-ink-100 transition-colors">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="text-xl font-semibold tracking-tight text-brand-900">
                Clinicest
            </a>

            <nav class="hidden items-center gap-8 lg:flex">
                <a href="{{ route('treatments.index') }}" class="text-sm font-medium text-ink-700 hover:text-brand-700">{{ __('nav.treatments') }}</a>
                <a href="{{ route('clinics.index') }}" class="text-sm font-medium text-ink-700 hover:text-brand-700">{{ __('nav.clinics') }}</a>
                <a href="{{ route('doctors.index') }}" class="text-sm font-medium text-ink-700 hover:text-brand-700">{{ __('nav.doctors') }}</a>
                <a href="{{ route('reviews.index') }}" class="text-sm font-medium text-ink-700 hover:text-brand-700">{{ __('nav.reviews') }}</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('get-quote') }}"
                   class="hidden rounded-md bg-brand-700 px-4 py-2 text-sm font-semibold text-white shadow-card transition hover:bg-brand-800 sm:inline-flex">
                    {{ __('nav.get_quote') }}
                </a>
                <button @click="mobileOpen = !mobileOpen" class="lg:hidden" aria-label="{{ __('Menu') }}">
                    <svg class="h-6 w-6 text-ink-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>

        <nav x-show="mobileOpen" x-cloak class="border-t border-ink-100 bg-white px-4 py-4 lg:hidden">
            <a href="{{ route('treatments.index') }}" class="block py-2 text-sm font-medium text-ink-700">{{ __('nav.treatments') }}</a>
            <a href="{{ route('clinics.index') }}" class="block py-2 text-sm font-medium text-ink-700">{{ __('nav.clinics') }}</a>
            <a href="{{ route('doctors.index') }}" class="block py-2 text-sm font-medium text-ink-700">{{ __('nav.doctors') }}</a>
            <a href="{{ route('reviews.index') }}" class="block py-2 text-sm font-medium text-ink-700">{{ __('nav.reviews') }}</a>
        </nav>
    </header>

    <main id="main">
        {{ $slot }}
    </main>

    <footer class="border-t border-ink-100 bg-ink-50">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                <div>
                    <h3 class="text-sm font-semibold text-ink-900">{{ __('nav.treatments') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li><a href="{{ route('treatments.index') }}" class="hover:text-brand-700">{{ __('nav.treatments') }}</a></li>
                        <li><a href="{{ route('clinics.index') }}" class="hover:text-brand-700">{{ __('nav.clinics') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink-900">{{ __('Company') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li><a href="{{ route('about') }}" class="hover:text-brand-700">{{ __('nav.about') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-brand-700">{{ __('nav.contact') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-ink-900">{{ __('Trust') }}</h3>
                    <ul class="mt-4 space-y-2 text-sm text-ink-600">
                        <li><a href="{{ route('faq') }}" class="hover:text-brand-700">{{ __('nav.faq') }}</a></li>
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
        <a href="{{ route('get-quote') }}" class="flex-1 rounded-md bg-brand-700 py-2.5 text-center text-sm font-semibold text-white">
            {{ __('nav.get_quote') }}
        </a>
    </div>

    @livewireScripts
</body>
</html>
