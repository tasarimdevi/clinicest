<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen items-center justify-center bg-ink-50 text-ink-800 antialiased">

    <div class="w-full max-w-sm px-4">
        <div class="mb-8 flex items-center justify-center gap-2.5 text-center">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-2xl font-semibold tracking-tight text-brand-900">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-[#E8E3D8]">
                    <svg class="h-6 w-6" viewBox="0 0 100 100" aria-hidden="true">
                        <circle cx="50" cy="37" r="5.5" class="fill-gold-400"/>
                        <path d="M31 49 Q50 72 69 49" fill="none" stroke-width="9" stroke-linecap="round" class="stroke-brand-950"/>
                    </svg>
                </span>
                Clinicest
            </a>
        </div>
        <div class="rounded-lg border border-ink-200 bg-white p-8 shadow-card">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
