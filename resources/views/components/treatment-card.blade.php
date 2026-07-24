@props(['treatment', 'code' => null])

{{--
    "Manifest gate" card — echoes the departure-board motif from the
    homepage hero (see docs/03-design-system.md §1). $code is an optional
    boarding-gate-style label (e.g. "GATE DI-01"); falls back to a code
    derived from the treatment's own slug so every card gets one for free.
    No border of its own — meant to sit in a grid whose parent draws
    hairline dividers via a 1px gap + background (see home-page.blade.php),
    the same technique as the homepage prototype's `.manifest` grid.
--}}
@php
    $gateCode = $code ?? 'GATE '.strtoupper(substr(preg_replace('/[^a-z]/', '', $treatment->slug), 0, 2)).'-'.str_pad((string) $treatment->id, 2, '0', STR_PAD_LEFT);
@endphp

<a href="{{ route('treatments.show', $treatment->slug) }}"
   class="group flex flex-col gap-2.5 bg-white p-6 transition hover:bg-teal-50/40">
    <span class="font-mono text-[0.68rem] tracking-wide text-ink-400">{{ $gateCode }}</span>
    <h3 class="text-base font-semibold text-ink-900 group-hover:text-brand-600">
        {{ $treatment->getTranslation('name', app()->getLocale()) }}
    </h3>
    @if ($treatment->base_price_min)
        <p class="font-mono text-sm font-semibold text-teal-600 tabular-nums">
            {{ __('from') }} {{ $treatment->currency }} {{ number_format($treatment->base_price_min / 100, 0) }}
        </p>
    @endif
</a>
