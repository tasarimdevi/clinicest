@props(['delay' => 0])

{{--
    Fades + slides its content up when it scrolls into view. Self-contained
    (IntersectionObserver in x-init, no Alpine plugin). If IntersectionObserver
    is unavailable, or the visitor prefers reduced motion, the content is
    shown immediately, so it degrades safely. A 2.5s grace-period fallback
    also force-shows the content even if the observer never fires (e.g. a
    full-page screenshot tool that expands the viewport programmatically
    without dispatching real scroll/intersection events) — content should
    never be stuck invisible just because a scroll never "really" happened.

    The caller's classes (often a grid) are merged into the same element via
    $attributes->merge — a separate literal class="" would emit a duplicate
    class attribute and the caller's grid would be silently dropped.
--}}
<div x-data="{ shown: false }"
     x-init="if (! window.IntersectionObserver || matchMedia('(prefers-reduced-motion: reduce)').matches) { shown = true; return; }
             const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) { shown = true; o.disconnect(); } }, { threshold: 0.12 });
             o.observe($el);
             setTimeout(() => { shown = true; o.disconnect(); }, 2500);"
     x-bind:class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
     @if ((int) $delay > 0) style="transition-delay: {{ (int) $delay }}ms" @endif
     {{ $attributes->merge(['class' => 'transition duration-700 ease-out']) }}>
    {{ $slot }}
</div>
