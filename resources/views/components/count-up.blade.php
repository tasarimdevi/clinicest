@props([
    'to',            // target number, e.g. 1240 or 4.9
    'suffix' => '',  // e.g. '+' or '%'
    'decimals' => 0,
    'duration' => 1400,
])

{{--
    Eases a number from 0 to :to when it scrolls into view (cubic ease-out,
    no Alpine plugin — a plain IntersectionObserver + rAF). Renders the
    target value as static text first so it's correct with JS disabled and
    for crawlers; the animation only enhances.
--}}
<span x-data="{
        done: false,
        display: @js(number_format((float) $to, $decimals)) + @js($suffix),
        run() {
            const target = {{ (float) $to }};
            const start = performance.now();
            const tick = (now) => {
                const p = Math.min((now - start) / {{ (int) $duration }}, 1);
                const val = target * (1 - Math.pow(1 - p, 3));
                this.display = val.toLocaleString(undefined, {
                    minimumFractionDigits: {{ (int) $decimals }},
                    maximumFractionDigits: {{ (int) $decimals }},
                }) + @js($suffix);
                if (p < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
        }
     }"
      x-init="const o = new IntersectionObserver(([e]) => { if (e.isIntersecting && !done) { done = true; run(); o.disconnect(); } }, { threshold: 0.5 }); o.observe($el);"
      x-text="display"
      {{ $attributes }}>{{ number_format((float) $to, $decimals).$suffix }}</span>
