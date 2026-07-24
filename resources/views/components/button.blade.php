@props([
    'variant' => 'primary', // primary | secondary | ghost | gold | danger
    'size' => 'md', // sm | md | lg
    'as' => 'button', // button | a
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-brand-700 text-white hover:bg-brand-800 focus-visible:ring-brand-500/40',
        'secondary' => 'bg-white text-brand-700 border border-brand-200 hover:bg-brand-50 focus-visible:ring-brand-500/40',
        'ghost' => 'bg-transparent text-ink-700 hover:bg-ink-100 focus-visible:ring-ink-400/40',
        'gold' => 'bg-gold-500 text-white hover:bg-gold-600 focus-visible:ring-gold-400/40',
        'danger' => 'bg-danger-500 text-white hover:bg-danger-600 focus-visible:ring-danger-500/40',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-md font-semibold shadow-card transition
        focus-visible:outline-none focus-visible:ring-4 disabled:opacity-50 disabled:pointer-events-none '
        . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if ($as === 'a')
    <a {{ $attributes->merge(['class' => $classes, 'href' => $href]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        {{ $slot }}
    </button>
@endif
