<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? 'Dashboard').' | '.config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-ink-50 text-ink-800 antialiased" x-data="{ sidebarOpen: false }">

    @php
        $currentClinic = request()->route('clinic');
        $navUser = auth()->user();

        $navItems = match (true) {
            request()->routeIs('admin.*') => array_values(array_filter([
                ['label' => __('Dashboard'), 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard'],
                $navUser?->can('leads.view')
                    ? ['label' => __('Leads'), 'route' => 'admin.leads.index', 'pattern' => 'admin.leads.*']
                    : null,
                $navUser?->can('clinics.view')
                    ? ['label' => __('Clinics'), 'route' => 'admin.clinics.index', 'pattern' => 'admin.clinics.*']
                    : null,
                $navUser?->can('clinics.verify')
                    ? ['label' => __('Applications'), 'route' => 'admin.clinics.applications', 'pattern' => 'admin.clinics.applications']
                    : null,
                $navUser?->can('doctors.view')
                    ? ['label' => __('Doctors'), 'route' => 'admin.doctors.index', 'pattern' => 'admin.doctors.*']
                    : null,
                $navUser?->can('content.view')
                    ? ['label' => __('Content'), 'route' => 'admin.posts.index', 'pattern' => 'admin.posts.*']
                    : null,
                $navUser?->can('content.view')
                    ? ['label' => __('Treatments'), 'route' => 'admin.treatments.index', 'pattern' => 'admin.treatments.*']
                    : null,
                $navUser?->can('content.view')
                    ? ['label' => __('FAQs'), 'route' => 'admin.faqs.index', 'pattern' => 'admin.faqs.*']
                    : null,
                $navUser?->can('content.view')
                    ? ['label' => __('Categories'), 'route' => 'admin.categories.index', 'pattern' => 'admin.categories.*']
                    : null,
                $navUser?->can('reviews.moderate')
                    ? ['label' => __('Reviews'), 'route' => 'admin.reviews.index', 'pattern' => 'admin.reviews.*']
                    : null,
                $navUser?->can('reviews.moderate')
                    ? ['label' => __('Before / After'), 'route' => 'admin.before-after.index', 'pattern' => 'admin.before-after.*']
                    : null,
                $navUser?->can('billing.view')
                    ? ['label' => __('Billing'), 'route' => 'admin.billing.index', 'pattern' => 'admin.billing.*']
                    : null,
                $navUser?->can('settings.manage')
                    ? ['label' => __('Chat Assistant'), 'route' => 'admin.chat-settings.index', 'pattern' => 'admin.chat-settings.*']
                    : null,
            ])),
            request()->routeIs('clinic.*') && $currentClinic => array_values(array_filter([
                ['label' => __('Dashboard'), 'route' => 'clinic.dashboard', 'params' => [$currentClinic], 'pattern' => 'clinic.dashboard'],
                ['label' => __('Lead Inbox'), 'route' => 'clinic.leads', 'params' => [$currentClinic], 'pattern' => 'clinic.leads'],
                $navUser?->can('clinics.manage')
                    ? ['label' => __('Analytics'), 'route' => 'clinic.analytics', 'params' => [$currentClinic], 'pattern' => 'clinic.analytics']
                    : null,
                $navUser?->can('documents.view')
                    ? ['label' => __('Documents'), 'route' => 'clinic.documents.index', 'params' => [$currentClinic], 'pattern' => 'clinic.documents.*']
                    : null,
                $navUser?->can('clinics.manage')
                    ? ['label' => __('Profile'), 'route' => 'clinic.profile', 'params' => [$currentClinic], 'pattern' => 'clinic.profile']
                    : null,
                $navUser?->can('clinics.manage')
                    ? ['label' => __('Before / After'), 'route' => 'clinic.before-after', 'params' => [$currentClinic], 'pattern' => 'clinic.before-after']
                    : null,
                $navUser?->can('billing.view')
                    ? ['label' => __('Billing'), 'route' => 'clinic.billing', 'params' => [$currentClinic], 'pattern' => 'clinic.billing']
                    : null,
            ])),
            default => [
                ['label' => __('Dashboard'), 'route' => 'patient.dashboard', 'pattern' => 'patient.dashboard'],
            ],
        };
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-30 w-64 transform border-r border-ink-200 bg-white transition-transform lg:static lg:translate-x-0">
            <div class="flex h-16 items-center gap-2 border-b border-ink-100 px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold tracking-tight text-brand-900">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#E8E3D8]">
                        <svg class="h-5 w-5" viewBox="0 0 100 100" aria-hidden="true">
                            <circle cx="50" cy="37" r="5.5" class="fill-gold-400"/>
                            <path d="M31 49 Q50 72 69 49" fill="none" stroke-width="9" stroke-linecap="round" class="stroke-brand-950"/>
                        </svg>
                    </span>
                    Clinicest
                </a>
            </div>
            <nav class="space-y-1 p-4">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                       @class([
                           'block rounded-md px-3 py-2 text-sm font-medium',
                           'bg-brand-50 text-brand-700' => request()->routeIs($item['pattern']),
                           'text-ink-600 hover:bg-ink-50' => ! request()->routeIs($item['pattern']),
                       ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="flex flex-1 flex-col lg:pl-0">
            {{-- Top bar --}}
            <header class="flex h-16 items-center justify-between border-b border-ink-200 bg-white px-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden" aria-label="{{ __('Menu') }}">
                        <svg class="h-6 w-6 text-ink-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                    <h1 class="text-base font-semibold text-ink-900">{{ $title ?? __('Dashboard') }}</h1>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1 text-sm font-medium">
                        @foreach (config('clinicest.locales.supported', ['en']) as $locale)
                            @if (! $loop->first)
                                <span class="text-ink-300">|</span>
                            @endif
                            <a href="{{ route('settings.locale', $locale) }}"
                               @class([
                                   'px-1 uppercase',
                                   'text-brand-700' => app()->getLocale() === $locale,
                                   'text-ink-400 hover:text-brand-700' => app()->getLocale() !== $locale,
                               ])>{{ $locale }}</a>
                        @endforeach
                    </div>
                    <livewire:notification-bell />
                    <a href="{{ route('settings.notifications') }}" class="hidden text-sm text-ink-500 hover:text-brand-700 sm:inline">{{ auth()->user()?->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-ink-500 hover:text-brand-700">
                            {{ __('Sign out') }}
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
