<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="flex min-h-screen items-center justify-center bg-ink-50 text-ink-800 antialiased">

    <div class="w-full max-w-sm px-4">
        <div class="mb-8 text-center">
            <a href="{{ route('home') }}" class="text-2xl font-semibold tracking-tight text-brand-900">Clinicest</a>
        </div>
        <div class="rounded-lg border border-ink-200 bg-white p-8 shadow-card">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
