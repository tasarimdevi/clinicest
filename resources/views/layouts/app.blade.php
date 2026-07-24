<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($title ?? 'Dashboard').' | '.config('app.name') }}</title>

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
                $navUser?->can('doctors.view')
                    ? ['label' => __('Doctors'), 'route' => 'admin.doctors.index', 'pattern' => 'admin.doctors.*']
                    : null,
            ])),
            request()->routeIs('clinic.*') && $currentClinic => [
                ['label' => __('Dashboard'), 'route' => 'clinic.dashboard', 'params' => [$currentClinic], 'pattern' => 'clinic.dashboard'],
                ['label' => __('Lead Inbox'), 'route' => 'clinic.leads', 'params' => [$currentClinic], 'pattern' => 'clinic.leads'],
            ],
            default => [
                ['label' => __('Dashboard'), 'route' => 'patient.dashboard', 'pattern' => 'patient.dashboard'],
            ],
        };
    @endphp

    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-30 w-64 transform border-r border-ink-200 bg-white transition-transform lg:static lg:translate-x-0">
            <div class="flex h-16 items-center border-b border-ink-100 px-6">
                <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-brand-900">Clinicest</a>
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
                    <span class="hidden text-sm text-ink-500 sm:inline">{{ auth()->user()?->name }}</span>
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
