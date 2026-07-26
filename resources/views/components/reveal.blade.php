@props(['delay' => 0])

{{--
    Fades + slides its content up when it scrolls into view. Self-contained
    (IntersectionObserver in x-init, no Alpine plugin). If IntersectionObserver
    is unavailable the content is shown immediately, so it degrades safely.
--}}
<div x-data="{ shown: false }"
     x-init="if (! window.IntersectionObserver) { shown = true; return; }
             const o = new IntersectionObserver(([e]) => { if (e.isIntersecting) { shown = true; o.disconnect(); } }, { threshold: 0.12 });
             o.observe($el);"
     x-bind:class="shown ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
     class="transition duration-700 ease-out"
     @if ((int) $delay > 0) style="transition-delay: {{ (int) $delay }}ms" @endif
     {{ $attributes }}>
    {{ $slot }}
</div>
