<div>
    {{--
        Clinicest homepage — "travel dossier" identity taken cinematic.
        Navy (brand-950) + porcelain + gold + verification teal, per
        docs/03-design-system.md. Photography is photo-ready: every image
        block has correct aspect + object-cover and falls back to an
        on-brand gradient/monogram when no real asset exists yet, so the
        page never fabricates stock imagery (guiding constraint §3.1).

        Motion: <x-reveal>/<x-count-up> (IntersectionObserver) + the cx-*
        utilities in app.css — all disabled under prefers-reduced-motion.
    --}}

    {{-- ══════════════════════════ HERO ══════════════════════════ --}}
    <section class="relative overflow-hidden bg-brand-950 text-ink-50">
        @if ($heroImage)
            {{-- Real photo, dropped at public/images/home/hero.{webp|jpg|png}
                 (see HomePage::marketingImage). A lighter wash than before —
                 heavy enough for WCAG text contrast on the left, but the photo
                 (and a teal/gold color wash on top of it) now actually reads
                 as a photo instead of being crushed to near-black. --}}
            <img src="{{ $heroImage }}" alt=""
                 class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-brand-950 via-brand-950/60 to-brand-950/20"></div>
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-brand-950/95 via-transparent to-brand-950/10"></div>
        @else
            {{-- Cinematic fallback ground: vivid royal-blue gradient + dot grid
                 + soft glows, used until a real photo is placed. Glow is
                 brand-blue (rgba(95,140,203,...) = brand-400), not teal —
                 a teal wash this large reads as blue-green, not "vivid blue". --}}
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-700 via-brand-900 to-brand-950"></div>
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(120%_80%_at_15%_-10%,rgba(95,140,203,0.55),transparent_55%),radial-gradient(90%_70%_at_100%_10%,rgba(199,155,87,0.32),transparent_50%)]"></div>
        @endif
        <div class="pointer-events-none absolute inset-0 opacity-[0.14]" style="background-image: radial-gradient(circle, rgba(255,255,255,0.22) 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="animate-cx-float pointer-events-none absolute -left-24 top-24 h-96 w-96 rounded-full bg-brand-400/30 blur-3xl"></div>
        <div class="animate-cx-float-slow pointer-events-none absolute -right-28 top-1/3 h-[28rem] w-[28rem] rounded-full bg-gold-400/20 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-4 pb-20 pt-16 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:gap-10 lg:px-8 lg:pb-28 lg:pt-24">
            {{-- Left: editorial copy --}}
            <div>
                <x-reveal>
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 font-mono text-[0.7rem] font-semibold uppercase tracking-widest text-gold-300 backdrop-blur">
                        <span class="relative flex h-1.5 w-1.5">
                            <span class="animate-cx-ping absolute inline-flex h-full w-full rounded-full bg-teal-400"></span>
                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-teal-300"></span>
                        </span>
                        {{ __('home.hero_overline') }}
                    </span>

                    <h1 class="mt-6 font-serif text-[2.6rem] font-medium leading-[1.05] tracking-tight sm:text-6xl lg:text-[4.2rem]">
                        {{ __('home.hero_title') }}
                    </h1>

                    <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink-300">
                        {{ __('home.hero_subtitle') }}
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                        <a href="{{ route('get-quote') }}"
                           class="cx-lift cx-shimmer group inline-flex items-center justify-center gap-2 rounded-xl bg-gold-500 px-7 py-4 text-base font-semibold text-brand-950 shadow-hero transition hover:bg-gold-400">
                            {{ __('home.hero_cta') }}
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
                        </a>
                        <a href="https://wa.me/" target="_blank" rel="noopener"
                           class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/5 px-7 py-4 text-base font-semibold text-ink-50 backdrop-blur transition hover:bg-white/10">
                            <svg class="h-5 w-5 text-teal-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.29-1.39a9.9 9.9 0 0 0 4.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91C21.96 6.45 17.5 2 12.04 2z"/></svg>
                            {{ __('home.hero_whatsapp') }}
                        </a>
                    </div>

                    {{-- Video: "watch a patient story" --}}
                    <button type="button" x-data @click="$dispatch('open-story')"
                            class="group mt-7 inline-flex items-center gap-3 text-left text-ink-200 transition hover:text-ink-50">
                        <span class="relative flex h-12 w-12 items-center justify-center rounded-full border border-white/25 bg-white/5 backdrop-blur transition group-hover:bg-white/10">
                            <span class="animate-cx-ping absolute inline-flex h-full w-full rounded-full border border-white/30"></span>
                            <svg class="ml-0.5 h-5 w-5 text-gold-300" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">{{ __('home.hero_watch') }}</span>
                            <span class="block font-mono text-[0.7rem] uppercase tracking-wider text-ink-400">{{ __('home.hero_watch_sub') }}</span>
                        </span>
                    </button>

                    {{-- Trust chips --}}
                    <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3 border-t border-white/10 pt-6 text-sm text-ink-300">
                        @if ($stats['avgRating'])
                            <span class="flex items-center gap-1.5 font-mono tabular-nums text-gold-300">
                                ★ <x-count-up :to="$stats['avgRating']" :decimals="1" class="font-semibold" />
                                <span class="text-ink-400">/ 5</span>
                            </span>
                        @endif
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @if ($stats['clinics'] > 0)<x-count-up :to="$stats['clinics']" class="font-semibold text-ink-50" />@endif
                            {{ __('home.trust_verified_clinics') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('home.trust_price_guarantee') }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            {{ __('home.trust_gdpr') }}
                        </span>
                    </div>
                </x-reveal>
            </div>

            {{-- Right: floating interactive price-comparison dashboard --}}
            <x-reveal :delay="150" class="lg:justify-self-end">
                @php
                    // Illustrative €-normalised prices for a representative case.
                    // Clearly labelled as illustrative — real pricing is confirmed
                    // per-case (trust-integrity constraint, docs/10-roadmap.md §3).
                    $turkey = 4200;
                @endphp
                <div x-data="{
                        turkey: {{ $turkey }},
                        homes: { uk: { flag: '🇬🇧', label: 'United Kingdom', price: 13500 }, de: { flag: '🇩🇪', label: 'Germany', price: 11000 }, us: { flag: '🇺🇸', label: 'United States', price: 22000 } },
                        sel: 'uk',
                        get home() { return this.homes[this.sel]; },
                        get save() { return this.home.price - this.turkey; },
                        get pct() { return Math.round((this.save / this.home.price) * 100); },
                        get turkeyW() { return Math.round((this.turkey / this.home.price) * 100); },
                        fmt(n) { return '€' + n.toLocaleString('en-US'); }
                     }"
                     class="animate-cx-float-slow relative w-full max-w-md rounded-3xl border border-white/15 bg-white/10 p-6 shadow-hero backdrop-blur-2xl">
                    <div class="pointer-events-none absolute -right-3 -top-3 rounded-full bg-teal-500 px-3 py-1 font-mono text-[0.62rem] font-semibold uppercase tracking-wider text-white shadow-raised">
                        {{ __('home.pc_label') }}
                    </div>

                    <p class="font-mono text-[0.68rem] uppercase tracking-widest text-gold-300">{{ __('home.pc_title') }}</p>
                    <p class="mt-1 text-sm text-ink-200">{{ __('home.pc_treatment') }}</p>

                    {{-- Home-country toggle --}}
                    <div class="mt-4 flex gap-1 rounded-xl bg-brand-950/40 p-1 text-sm">
                        <template x-for="(h, key) in homes" :key="key">
                            <button type="button" @click="sel = key"
                                    class="flex-1 rounded-lg px-2 py-1.5 font-medium transition"
                                    :class="sel === key ? 'bg-white/90 text-brand-950 shadow' : 'text-ink-300 hover:text-ink-50'">
                                <span x-text="h.flag"></span>
                                <span class="ml-1 font-mono text-[0.7rem] uppercase" x-text="key"></span>
                            </button>
                        </template>
                    </div>

                    {{-- Price bars --}}
                    <div class="mt-5 space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-xs text-ink-300">
                                <span x-text="home.flag + ' ' + home.label"></span>
                                <span class="font-mono font-semibold tabular-nums text-ink-50" x-text="fmt(home.price)"></span>
                            </div>
                            <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-white/10">
                                <div class="h-full rounded-full bg-ink-300/60" style="width: 100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-teal-300">🇹🇷 {{ __('home.pc_you_pay') }}</span>
                                <span class="font-mono font-semibold tabular-nums text-teal-300" x-text="fmt(turkey)"></span>
                            </div>
                            <div class="mt-1.5 h-2.5 w-full overflow-hidden rounded-full bg-white/10">
                                <div class="cx-bar-grow h-full rounded-full bg-gradient-to-r from-teal-400 to-teal-300" :style="`width: ${turkeyW}%`"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Savings headline --}}
                    <div class="mt-5 flex items-end justify-between rounded-2xl bg-gradient-to-br from-teal-500/25 to-brand-900/40 p-4">
                        <div>
                            <p class="font-mono text-[0.62rem] uppercase tracking-widest text-ink-300">{{ __('home.pc_you_save') }}</p>
                            <p class="font-mono text-3xl font-bold tabular-nums text-teal-200" x-text="fmt(save)"></p>
                            <p class="text-[0.7rem] text-ink-300">{{ __('home.pc_vs') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-mono text-4xl font-bold tabular-nums text-gold-300"><span x-text="pct"></span>%</p>
                            <p class="text-[0.7rem] text-ink-300">↓</p>
                        </div>
                    </div>

                    {{-- Included details grid --}}
                    <dl class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2.5 text-xs">
                        @foreach ([
                            ['pc_flight', 'pc_flight_v', 'M2.5 19l19-7-19-7v5l13 2-13 2z'],
                            ['pc_hotel', 'pc_hotel_v', 'M3 21V7l9-4 9 4v14M9 21v-6h6v6M3 21h18'],
                            ['pc_transfer', 'pc_transfer_v', 'M5 17a2 2 0 104 0M15 17a2 2 0 104 0M3 13h18l-2-6H5L3 13zm0 0v4h2m14-4v4h-2'],
                            ['pc_duration', 'pc_duration_v', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['pc_recovery', 'pc_recovery_v', 'M3 12h4l3 8 4-16 3 8h4'],
                        ] as $row)
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-teal-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $row[2] }}"/></svg>
                                <span class="text-ink-300">{{ __('home.'.$row[0]) }}<br><b class="font-semibold text-ink-50">{{ __('home.'.$row[1]) }}</b></span>
                            </div>
                        @endforeach
                        <div class="flex items-start gap-2">
                            <span class="relative mt-1 flex h-2 w-2 shrink-0"><span class="animate-cx-ping absolute inline-flex h-full w-full rounded-full bg-teal-400"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-teal-300"></span></span>
                            <span class="text-ink-300">{{ __('home.pc_rating') }}<br><b class="font-mono font-semibold text-gold-300">★ {{ $stats['avgRating'] ? number_format($stats['avgRating'], 1) : '4.9' }}</b></span>
                        </div>
                    </dl>

                    <p class="mt-4 flex items-center gap-1.5 text-[0.66rem] leading-snug text-ink-400">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('home.pc_disclaimer') }}
                    </p>
                </div>
            </x-reveal>
        </div>

        {{-- Patient-story modal (placeholder poster; drop a real video later) --}}
        <div x-data="{ show: false }" x-cloak
             @open-story.window="show = true" @keydown.escape.window="show = false"
             x-show="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-brand-950/80 backdrop-blur-sm" @click="show = false"></div>
            <div x-show="show" x-transition.scale.origin-center
                 class="relative w-full max-w-3xl overflow-hidden rounded-2xl bg-brand-950 shadow-hero ring-1 ring-white/10">
                <div class="relative flex aspect-video items-center justify-center bg-gradient-to-br from-brand-900 to-brand-950">
                    <span class="pointer-events-none absolute inset-0 opacity-[0.12]" style="background-image: radial-gradient(circle, rgba(255,255,255,0.35) 1px, transparent 1px); background-size: 20px 20px;"></span>
                    <div class="relative text-center text-ink-200">
                        <svg class="mx-auto h-12 w-12 text-gold-300" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <p class="mt-3 font-serif text-lg">{{ __('home.hero_watch') }}</p>
                        <p class="mt-1 font-mono text-[0.7rem] uppercase tracking-wider text-ink-400">{{ __('Coming soon') }}</p>
                    </div>
                </div>
                <button type="button" @click="show = false" aria-label="{{ __('Close') }}"
                        class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-ink-50 backdrop-blur transition hover:bg-white/20">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </section>

    {{-- ══════════════════ PRESS / AS SEEN IN ══════════════════ --}}
    <section class="border-y border-ink-200 bg-white py-10">
        <p class="text-center font-mono text-[0.7rem] font-semibold uppercase tracking-widest text-ink-400">{{ __('home.press_title') }}</p>
        <div class="group relative mt-6 overflow-hidden [mask-image:linear-gradient(90deg,transparent,#000_12%,#000_88%,transparent)]">
            <div class="animate-cx-marquee flex w-max items-center gap-14 group-hover:[animation-play-state:paused]">
                @foreach (['BBC', 'CNN', 'Forbes', 'Daily Mail', 'The Guardian', 'Sky News', 'BBC', 'CNN', 'Forbes', 'Daily Mail', 'The Guardian', 'Sky News'] as $logo)
                    <span class="whitespace-nowrap font-serif text-2xl font-medium tracking-tight text-ink-300 transition hover:text-ink-500">{{ $logo }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════ STATS BAND ══════════════════════ --}}
    {{-- A rich, vivid navy band rather than a flat grey grid — the page's
         second "color beat" after the hero, so the scroll doesn't go dark→
         flat-grey→white→flat-grey with nothing alive in between. --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-900 to-brand-950 py-14 text-ink-50">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(80%_140%_at_0%_50%,rgba(95,140,203,0.5),transparent_60%),radial-gradient(80%_140%_at_100%_50%,rgba(199,155,87,0.3),transparent_60%)]"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.1]" style="background-image: radial-gradient(circle, rgba(255,255,255,0.25) 1px, transparent 1px); background-size: 22px 22px;"></div>

        {{-- Cells are transparent (not a solid fill) so the gradient/glow
             above shows through everywhere — a solid bg here would sit on
             top and hide the whole effect. gap-px + bg-white/15 on the grid
             draws the hairline dividers instead. --}}
        <div class="relative mx-auto grid max-w-7xl grid-cols-2 gap-px overflow-hidden rounded-2xl border border-white/10 bg-white/15 sm:grid-cols-4">
            @php
                $statCards = [
                    ['v' => $stats['clinics'] ?: 40, 'suffix' => '+', 'dec' => 0, 'label' => __('home.stats_clinics')],
                    ['v' => $stats['reviews'] ?: 1200, 'suffix' => '+', 'dec' => 0, 'label' => __('home.stats_reviews')],
                    ['v' => 65, 'suffix' => '%', 'dec' => 0, 'label' => __('home.stats_saving')],
                    ['v' => null, 'text' => __('home.stats_response_v'), 'label' => __('home.stats_response')],
                ];
            @endphp
            @foreach ($statCards as $s)
                <div class="bg-brand-900/40 px-6 py-8 text-center backdrop-blur-sm">
                    <p class="font-mono text-4xl font-bold tabular-nums text-gold-300 sm:text-5xl">
                        @if ($s['v'] !== null)
                            <x-count-up :to="$s['v']" :suffix="$s['suffix']" :decimals="$s['dec']" />
                        @else
                            {{ $s['text'] }}
                        @endif
                    </p>
                    <p class="mt-2 text-sm text-ink-300">{{ $s['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ══════════════════════ WHY TURKEY ══════════════════════ --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('home.why_turkey_eyebrow') }}</p>
            <h2 class="mt-3 font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('home.why_turkey_title') }}</h2>
            <p class="mt-4 text-lg text-ink-600">{{ __('home.why_turkey_subtitle') }}</p>
        </div>

        @php
            // Rotating accent per card (teal / gold / brand) instead of a
            // single monochrome icon color — reads as more varied/alive
            // across a 6-card grid, per the reference's colorful icon set.
            // Bold, solid badge colors BY DEFAULT (not hover-only — a pale
            // -50 tint only visible on :hover reads as flat/monochrome in a
            // static view, which is exactly what looked "lifeless" before).
            $accents = [
                ['bg' => 'bg-teal-500', 'text' => 'text-white', 'hover' => 'group-hover:bg-teal-600', 'border' => 'hover:border-teal-200'],
                ['bg' => 'bg-gold-500', 'text' => 'text-brand-950', 'hover' => 'group-hover:bg-gold-600', 'border' => 'hover:border-gold-300'],
                ['bg' => 'bg-brand-600', 'text' => 'text-white', 'hover' => 'group-hover:bg-brand-700', 'border' => 'hover:border-brand-200'],
            ];
        @endphp
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['wt_1_t', 'wt_1_d', 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'],
                ['wt_2_t', 'wt_2_d', 'M9 12l2 2 4-4m5 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['wt_3_t', 'wt_3_d', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['wt_4_t', 'wt_4_d', 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                ['wt_5_t', 'wt_5_d', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['wt_6_t', 'wt_6_d', 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ] as $i => $card)
                @php $a = $accents[$i % 3]; @endphp
                <x-reveal :delay="$i * 60"
                    class="cx-lift group rounded-2xl border border-ink-200 bg-white p-7 shadow-card transition-colors hover:shadow-raised {{ $a['border'] }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl {{ $a['bg'] }} {{ $a['text'] }} shadow-card transition {{ $a['hover'] }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card[2] }}"/></svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-ink-900">{{ __('home.'.$card[0]) }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ __('home.'.$card[1]) }}</p>
                </x-reveal>
            @endforeach
        </div>
    </section>

    {{-- ═══════════════════ HOW IT WORKS ═══════════════════ --}}
    <section class="relative overflow-hidden border-y border-ink-200 bg-gradient-to-br from-brand-700 via-brand-900 to-brand-950 py-20 text-ink-50">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(70%_60%_at_90%_0%,rgba(199,155,87,0.32),transparent_55%),radial-gradient(70%_60%_at_5%_100%,rgba(95,140,203,0.45),transparent_55%)]"></div>
        <div class="animate-cx-float-slow pointer-events-none absolute -right-20 bottom-0 h-72 w-72 rounded-full bg-brand-400/25 blur-3xl"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-300">{{ __('nav.how_it_works') }}</p>
                <h2 class="mt-3 font-serif text-3xl font-medium sm:text-4xl">{{ __('home.how_it_works_title') }}</h2>
                <p class="mt-4 text-lg text-ink-300">{{ __('home.how_it_works_subtitle') }}</p>
            </div>

            <div class="relative mt-14">
                <div class="pointer-events-none absolute inset-x-0 top-7 hidden border-t border-dashed border-white/20 lg:block"></div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-6 lg:gap-4">
                    @foreach ([
                        ['hiw_1_t', 'hiw_1_d', 'M4 16l4.5-4.5 3 3L20 6M4 20h16'],
                        ['hiw_2_t', 'hiw_2_d', 'M9.75 3.104a2.25 2.25 0 014.5 0M12 3v2m0 14a7 7 0 100-14 7 7 0 000 14z'],
                        ['hiw_3_t', 'hiw_3_d', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ['hiw_4_t', 'hiw_4_d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['hiw_5_t', 'hiw_5_d', 'M2.5 19l19-7-19-7v5l13 2-13 2z'],
                        ['hiw_6_t', 'hiw_6_d', 'M8 14s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ] as $i => $step)
                        <x-reveal :delay="$i * 70" class="relative text-center lg:text-left">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-gold-400/50 bg-brand-900 text-gold-300 shadow-hero lg:mx-0">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $step[2] }}"/></svg>
                            </div>
                            <span class="mt-4 block font-mono text-[0.68rem] font-semibold tracking-widest text-gold-300">STEP {{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-1 text-base font-semibold text-ink-50">{{ __('home.'.$step[0]) }}</h3>
                            <p class="mt-1.5 text-sm text-ink-300">{{ __('home.'.$step[1]) }}</p>
                        </x-reveal>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════ TREATMENTS ══════════════════════ --}}
    <section id="treatments" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div class="max-w-2xl">
                <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('home.treatments_eyebrow') }}</p>
                <h2 class="mt-3 font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('nav.treatments') }}</h2>
                <p class="mt-4 text-ink-600">{{ __('home.treatments_subtitle') }}</p>
            </div>
            <a href="{{ route('treatments.index') }}" class="group inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-600">
                {{ __('home.treatments_cta') }}
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
            </a>
        </div>

        <x-reveal class="mt-10 grid grid-cols-1 gap-px overflow-hidden rounded-2xl border border-ink-200 bg-ink-200 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredTreatments as $treatment)
                <x-treatment-card :treatment="$treatment" />
            @empty
                <p class="col-span-full bg-white p-6 text-sm text-ink-500">{{ __('home.treatments_empty') }}</p>
            @endforelse
        </x-reveal>
    </section>

    {{-- ═══════════════════ BEFORE / AFTER ═══════════════════ --}}
    @if ($beforeAfterCases->isNotEmpty())
        @php $baTreatments = $beforeAfterCases->pluck('treatment')->filter()->unique('id'); @endphp
        <section class="border-y border-ink-200 bg-ink-50 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"
                 x-data="{ filter: 'all' }">
                <div class="max-w-2xl">
                    <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('home.ba_eyebrow') }}</p>
                    <h2 class="mt-3 font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('home.ba_title') }}</h2>
                    <p class="mt-4 text-ink-600">{{ __('home.ba_subtitle') }}</p>
                </div>

                @if ($baTreatments->count() > 1)
                    <div class="mt-8 flex flex-wrap gap-2">
                        <button type="button" @click="filter = 'all'"
                                class="rounded-full border px-4 py-1.5 text-sm font-medium transition"
                                :class="filter === 'all' ? 'border-brand-600 bg-brand-600 text-white' : 'border-ink-200 bg-white text-ink-600 hover:border-ink-300'">
                            {{ __('All') }}
                        </button>
                        @foreach ($baTreatments as $t)
                            <button type="button" @click="filter = '{{ $t->id }}'"
                                    class="rounded-full border px-4 py-1.5 text-sm font-medium transition"
                                    :class="filter === '{{ $t->id }}' ? 'border-brand-600 bg-brand-600 text-white' : 'border-ink-200 bg-white text-ink-600 hover:border-ink-300'">
                                {{ $t->getTranslation('name', app()->getLocale()) }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($beforeAfterCases as $case)
                        <div x-show="filter === 'all' || filter === '{{ $case->treatment_id }}'" x-transition>
                            <x-before-after-slider :case="$case" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 text-center">
                    <a href="{{ route('before-after.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-600">
                        {{ __('home.ba_cta') }}
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════ FEATURED CLINICS ═══════════════════ --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <h2 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('home.featured_clinics_title') }}</h2>
            <p class="mt-4 text-ink-600">{{ __('home.featured_clinics_subtitle') }}</p>
        </div>
        <x-reveal class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($featuredClinics as $clinic)
                <x-clinic-card :clinic="$clinic" />
            @empty
                <p class="col-span-full text-sm text-ink-500">{{ __('home.clinics_empty') }}</p>
            @endforelse
        </x-reveal>
        <div class="mt-10">
            <a href="{{ route('clinics.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-600">
                {{ __('home.clinics_cta') }}
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
            </a>
        </div>
    </section>

    {{-- ══════════════════════ DOCTORS ══════════════════════ --}}
    @if ($doctors->isNotEmpty())
        <section class="border-t border-ink-200 bg-white py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('home.doctors_eyebrow') }}</p>
                    <h2 class="mt-3 font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('home.doctors_title') }}</h2>
                    <p class="mt-4 text-ink-600">{{ __('home.doctors_subtitle') }}</p>
                </div>
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($doctors as $doctor)
                        @php
                            $dName = $doctor->full_name;
                            $dInit = \Illuminate\Support\Str::of($dName)->explode(' ')->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->take(2)->implode('');
                            $dSpecialty = trim(implode(' ', array_filter($doctor->getTranslations('specialty'))));
                        @endphp
                        <x-reveal :delay="$loop->index * 60" class="cx-lift group overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-card hover:shadow-raised">
                            <div class="relative aspect-[4/5] w-full overflow-hidden">
                                @if ($doctor->photo_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($doctor->photo_path) }}" alt="{{ $dName }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-800 to-brand-950">
                                        <span class="font-serif text-4xl font-medium text-white/85">{{ $dInit ?: '—' }}</span>
                                    </div>
                                @endif
                                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-brand-950/85 to-transparent p-4">
                                    <p class="font-serif text-lg font-medium text-white">{{ $dName }}</p>
                                    @if ($dSpecialty)<p class="text-xs text-ink-200">{{ $dSpecialty }}</p>@endif
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-2 p-4 text-xs text-ink-500">
                                @if ($doctor->years_experience)
                                    <span class="inline-flex items-center gap-1"><svg class="h-3.5 w-3.5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $doctor->years_experience }}+ {{ __('yrs') }}</span>
                                @endif
                                @if (! empty($doctor->languages_json))
                                    <span class="font-mono uppercase tracking-wide text-ink-400">{{ collect($doctor->languages_json)->take(3)->map(fn ($l) => strtoupper($l))->implode(' · ') }}</span>
                                @endif
                            </div>
                        </x-reveal>
                    @endforeach
                </div>
                <div class="mt-10">
                    <a href="{{ route('doctors.index') }}" class="group inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-600">
                        {{ __('nav.doctors') }}
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ══════════════════════ REVIEWS ══════════════════════ --}}
    <section class="border-y border-ink-200 bg-ink-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('home.reviews_eyebrow') }}</p>
                <h2 class="mt-3 font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('home.reviews_title') }}</h2>
                <p class="mt-4 text-ink-600">{{ __('home.reviews_subtitle') }}</p>
            </div>

            @if ($reviews->isNotEmpty())
                @php
                    $avatarAccents = ['bg-teal-100 text-teal-700', 'bg-gold-100 text-gold-700', 'bg-brand-100 text-brand-800'];
                @endphp
                <div class="mt-10 columns-1 gap-6 sm:columns-2 lg:columns-3 [&>*]:mb-6 [&>*]:break-inside-avoid">
                    @foreach ($reviews as $review)
                        <x-reveal :delay="$loop->index * 50" class="cx-lift rounded-2xl border border-ink-200 bg-white p-6 shadow-card hover:shadow-raised">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-sm tabular-nums text-gold-500">
                                    @for ($s = 0; $s < 5; $s++){{ $s < round($review->rating) ? '★' : '☆' }}@endfor
                                </span>
                                @if ($review->is_verified)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-teal-50 px-2 py-0.5 text-[0.62rem] font-semibold text-teal-600">✓ {{ __('Verified treatment') }}</span>
                                @endif
                            </div>
                            @if ($review->title)
                                <p class="mt-3 font-semibold text-ink-900">{{ $review->title }}</p>
                            @endif
                            <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ \Illuminate\Support\Str::limit($review->body, 240) }}</p>
                            <div class="mt-4 flex items-center gap-3 border-t border-ink-100 pt-4">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $avatarAccents[$loop->index % 3] }} font-serif text-sm font-medium">
                                    {{ \Illuminate\Support\Str::substr($review->reviewer_name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-ink-900">{{ $review->reviewer_name }}</p>
                                    <p class="truncate text-xs text-ink-500">
                                        {{ $review->reviewerCountry?->name }}@if ($review->treatment) · {{ $review->treatment->getTranslation('name', app()->getLocale()) }}@endif
                                    </p>
                                </div>
                            </div>
                        </x-reveal>
                    @endforeach
                </div>
            @else
                <p class="mt-10 text-sm text-ink-500">{{ __('home.reviews_empty') }}</p>
            @endif
        </div>
    </section>

    {{-- ═══════════════ COST CALCULATOR TEASER ═══════════════ --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <x-reveal class="overflow-hidden rounded-3xl border border-gold-300/50 bg-gradient-to-br from-gold-50 via-white to-teal-50 shadow-card">
            <div class="grid items-center gap-8 p-8 lg:grid-cols-2 lg:p-12">
                <div>
                    <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('home.calc_eyebrow') }}</p>
                    <h2 class="mt-3 font-serif text-3xl font-medium text-ink-900">{{ __('home.calc_title') }}</h2>
                    <p class="mt-4 text-ink-600">{{ __('home.calc_subtitle') }}</p>
                    <a href="{{ route('cost-estimator') }}"
                       class="cx-lift mt-7 inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-3.5 text-base font-semibold text-white shadow-card transition hover:bg-brand-700">
                        {{ __('home.calc_cta') }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/></svg>
                    </a>
                </div>

                {{-- Inline illustrative estimator --}}
                <div x-data="{
                        tx: { implants: {label: '{{ __('home.pc_treatment') }}', home: 13500, tr: 4200}, veneers: {label: '{{ __('Veneers') }}', home: 9000, tr: 2800}, crowns: {label: '{{ __('Crowns') }}', home: 6000, tr: 1900} },
                        sel: 'implants',
                        get t() { return this.tx[this.sel]; },
                        get save() { return this.t.home - this.t.tr; },
                        get pct() { return Math.round((this.save / this.t.home) * 100); },
                        fmt(n){ return '€' + n.toLocaleString('en-US'); }
                     }"
                     class="rounded-2xl border border-ink-200 bg-white p-6 shadow-raised">
                    <label class="font-mono text-[0.68rem] font-semibold uppercase tracking-widest text-ink-400">{{ __('Treatment') }}</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <template x-for="(v, key) in tx" :key="key">
                            <button type="button" @click="sel = key"
                                    class="rounded-full border px-3.5 py-1.5 text-sm font-medium transition"
                                    :class="sel === key ? 'border-brand-600 bg-brand-600 text-white' : 'border-ink-200 text-ink-600 hover:border-ink-300'"
                                    x-text="v.label"></button>
                        </template>
                    </div>
                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div class="rounded-xl bg-ink-50 p-4">
                            <p class="text-xs text-ink-500">{{ __('Home country') }}</p>
                            <p class="mt-1 font-mono text-xl font-bold tabular-nums text-ink-700 line-through decoration-danger-500/60" x-text="fmt(t.home)"></p>
                        </div>
                        <div class="rounded-xl bg-teal-50 p-4">
                            <p class="text-xs text-teal-600">🇹🇷 {{ __('Turkey') }}</p>
                            <p class="mt-1 font-mono text-xl font-bold tabular-nums text-teal-600" x-text="fmt(t.tr)"></p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-between rounded-xl bg-brand-950 px-5 py-4 text-white">
                        <span class="text-sm text-ink-200">{{ __('home.pc_you_save') }}</span>
                        <span class="font-mono text-2xl font-bold tabular-nums text-gold-300"><span x-text="fmt(save)"></span> · <span x-text="pct"></span>%</span>
                    </div>
                    <p class="mt-3 text-[0.66rem] leading-snug text-ink-400">{{ __('home.pc_disclaimer') }}</p>
                </div>
            </div>
        </x-reveal>
    </section>

    {{-- ══════════════════ TRAVEL EXPERIENCE ══════════════════ --}}
    <section class="border-t border-ink-200 bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-600">{{ __('home.travel_eyebrow') }}</p>
                <h2 class="mt-3 font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('home.travel_title') }}</h2>
                <p class="mt-4 text-ink-600">{{ __('home.travel_subtitle') }}</p>
            </div>
            @php
                $teAccents = [
                    ['bg' => 'bg-teal-500', 'text' => 'text-white', 'hoverBorder' => 'hover:border-teal-200', 'hoverBg' => 'hover:bg-teal-50/40'],
                    ['bg' => 'bg-gold-500', 'text' => 'text-brand-950', 'hoverBorder' => 'hover:border-gold-300', 'hoverBg' => 'hover:bg-gold-50/50'],
                    ['bg' => 'bg-brand-600', 'text' => 'text-white', 'hoverBorder' => 'hover:border-brand-200', 'hoverBg' => 'hover:bg-brand-50/50'],
                ];
            @endphp
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['te_1_t', 'te_1_d', 'M2.5 19l19-7-19-7v5l13 2-13 2z'],
                    ['te_2_t', 'te_2_d', 'M3 21V7l9-4 9 4v14M9 21v-6h6v6'],
                    ['te_3_t', 'te_3_d', 'M5 17a2 2 0 104 0M15 17a2 2 0 104 0M3 13h18l-2-6H5L3 13z'],
                    ['te_4_t', 'te_4_d', 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9L21 21m-3-3l-2 2m2-2l-3-6-3 6'],
                    ['te_5_t', 'te_5_d', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                    ['te_6_t', 'te_6_d', 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'],
                ] as $i => $card)
                    @php $ta = $teAccents[$i % 3]; @endphp
                    <x-reveal :delay="$i * 50" class="flex items-start gap-4 rounded-2xl border border-ink-200 bg-ink-50/60 p-5 transition {{ $ta['hoverBorder'] }} {{ $ta['hoverBg'] }}">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $ta['bg'] }} {{ $ta['text'] }} shadow-card">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card[2] }}"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-ink-900">{{ __('home.'.$card[0]) }}</h3>
                            <p class="mt-1 text-sm text-ink-600">{{ __('home.'.$card[1]) }}</p>
                        </div>
                    </x-reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════ FAQ ══════════════════════════ --}}
    <section class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="font-serif text-3xl font-medium text-ink-900 sm:text-4xl">{{ __('home.faq_title') }}</h2>
            <p class="mt-4 text-ink-600">{{ __('home.faq_subtitle') }}</p>
        </div>
        <x-reveal class="mt-10">
            <x-faq-accordion :items="[
                ['q' => __('home.faq_1_q'), 'a' => __('home.faq_1_a')],
                ['q' => __('home.faq_2_q'), 'a' => __('home.faq_2_a')],
                ['q' => __('home.faq_3_q'), 'a' => __('home.faq_3_a')],
                ['q' => __('home.faq_4_q'), 'a' => __('home.faq_4_a')],
                ['q' => __('home.faq_5_q'), 'a' => __('home.faq_5_a')],
                ['q' => __('home.faq_6_q'), 'a' => __('home.faq_6_a')],
            ]" />
        </x-reveal>
    </section>

    {{-- ══════════════════════ FINAL CTA ══════════════════════ --}}
    <section class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-700 via-brand-900 to-brand-950 px-6 py-16 text-center text-ink-50 shadow-hero sm:px-16 sm:py-20">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(90%_120%_at_50%_-10%,rgba(199,155,87,0.4),transparent_55%),radial-gradient(80%_120%_at_50%_120%,rgba(95,140,203,0.5),transparent_55%)]"></div>
            <div class="animate-cx-float pointer-events-none absolute -left-16 top-10 h-64 w-64 rounded-full bg-brand-400/25 blur-3xl"></div>
            <div class="relative">
                <p class="font-mono text-xs font-semibold uppercase tracking-widest text-gold-300">{{ __('home.final_cta_eyebrow') }}</p>
                <h2 class="mx-auto mt-4 max-w-2xl font-serif text-4xl font-medium leading-tight sm:text-5xl">{{ __('home.final_cta_title') }}</h2>
                <p class="mt-5 font-mono text-sm tracking-wide text-ink-300">{{ __('home.final_cta_subtitle') }}</p>
                <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ route('get-quote') }}"
                       class="cx-lift inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gold-500 px-7 py-4 text-base font-semibold text-brand-950 shadow-hero transition hover:bg-gold-400 sm:w-auto">
                        {{ __('home.hero_cta') }}
                    </a>
                    <a href="https://wa.me/" target="_blank" rel="noopener"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/5 px-7 py-4 text-base font-semibold text-ink-50 backdrop-blur transition hover:bg-white/10 sm:w-auto">
                        <svg class="h-5 w-5 text-teal-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.29-1.39a9.9 9.9 0 0 0 4.75 1.21h.01c5.46 0 9.9-4.45 9.9-9.91C21.96 6.45 17.5 2 12.04 2z"/></svg>
                        {{ __('home.final_cta_whatsapp') }}
                    </a>
                    <a href="{{ route('contact') }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-7 py-4 text-base font-semibold text-ink-200 transition hover:text-ink-50 sm:w-auto">
                        {{ __('home.final_cta_book') }} &rarr;
                    </a>
                </div>
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 border-t-2 border-dashed border-white/15"></div>
        </div>
    </section>
</div>
