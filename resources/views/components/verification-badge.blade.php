@props(['tier' => 'verified']) {{-- pending | verified | verified_plus | elite --}}

@php
    $config = [
        'pending' => ['label' => __('Pending Review'), 'class' => 'bg-ink-100 text-ink-600'],
        'verified' => ['label' => __('Verified'), 'class' => 'bg-success-500/10 text-success-600'],
        'verified_plus' => ['label' => __('Verified+'), 'class' => 'bg-teal-500/10 text-teal-500'],
        'elite' => ['label' => __('Elite Partner'), 'class' => 'bg-gold-500/10 text-gold-600'],
    ][$tier] ?? ['label' => __('Verified'), 'class' => 'bg-success-500/10 text-success-600'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {$config['class']}"]) }}>
    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 1.5a1 1 0 0 1 .8.4l1.4 1.9 2.3-.5a1 1 0 0 1 1.1.6l.9 2.2 2.2.9a1 1 0 0 1 .6 1.1l-.5 2.3 1.9 1.4a1 1 0 0 1 0 1.6l-1.9 1.4.5 2.3a1 1 0 0 1-.6 1.1l-2.2.9-.9 2.2a1 1 0 0 1-1.1.6l-2.3-.5-1.4 1.9a1 1 0 0 1-1.6 0l-1.4-1.9-2.3.5a1 1 0 0 1-1.1-.6l-.9-2.2-2.2-.9a1 1 0 0 1-.6-1.1l.5-2.3-1.9-1.4a1 1 0 0 1 0-1.6l1.9-1.4-.5-2.3a1 1 0 0 1 .6-1.1l2.2-.9.9-2.2a1 1 0 0 1 1.1-.6l2.3.5 1.4-1.9a1 1 0 0 1 .8-.4Zm3.7 6.4a.75.75 0 0 0-1.1-1l-3.6 4-1.6-1.6a.75.75 0 1 0-1 1l2.1 2.2c.3.3.8.3 1.1 0l4.1-4.6Z" clip-rule="evenodd" />
    </svg>
    {{ $config['label'] }}
</span>
