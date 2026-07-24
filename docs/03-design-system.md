# 03 · UI Design System

**Design language:** *Trusted Medical Luxury* — Apple-level simplicity, Stripe-quality layout discipline, Healthgrades clinical credibility. Calm, spacious, confident. Glassmorphism used sparingly (hero search, sticky bars, badges) — never decoration for its own sake.

## 1. Color palette

Professional blue + white base, subtle gold for premium/verification accents, clinical greens/reds for status only.

### Tokens (Tailwind `theme.extend.colors`)

```js
// tailwind.config.js (excerpt)
colors: {
  brand: {                 // primary — trust blue
    50:'#EFF5FF', 100:'#DBE8FE', 200:'#BFD7FE', 300:'#93BBFD',
    400:'#6098FA', 500:'#3B76F6', 600:'#2456EB', 700:'#1D43D8',
    800:'#1E3AAF', 900:'#1E3A8A', 950:'#172554',   // 700/900 = primary actions & headers
  },
  gold: {                  // premium / verified accent (use sparingly)
    50:'#FBF8EF', 300:'#E7CE8F', 400:'#DBBB6A',
    500:'#C9A24B', 600:'#AE8636', 700:'#8A691F',
  },
  ink: {                   // neutral text/surfaces (slate-based, warm-neutral)
    50:'#F8FAFC',100:'#F1F5F9',200:'#E2E8F0',300:'#CBD5E1',400:'#94A3B8',
    500:'#64748B',600:'#475569',700:'#334155',800:'#1E293B',900:'#0F172A',
  },
  success:{500:'#12A150',600:'#0E8A44'},   // verified / positive
  warning:{500:'#E8A317'},
  danger: {500:'#E5484D',600:'#C62A2F'},   // emergency / errors only
  teal:   {500:'#0EA5A5'},                 // secondary medical accent
}
```

### Usage rules
- **Primary action / links:** `brand-700`. Hover `brand-800`. Focus ring `brand-500/40`.
- **Headers & hero:** deep `brand-900` → `brand-950` gradients over white.
- **Gold:** only for verification tier ("Elite Partner"), premium badges, rating stars, subtle dividers. Never for body buttons.
- **Backgrounds:** white / `ink-50`. Cards white with `ink-200` border + soft shadow.
- **Status:** green verified, teal informational, amber pending, red emergency/error only.
- **Contrast:** all text ≥ WCAG AA (body ≥ 4.5:1). Never gold text on white for body.

### Dark mode
Optional for app/dashboards. Surfaces `ink-900/950`, text `ink-100`, brand shifts to `brand-400/500` for adequate contrast. Marketing pages stay light by default.

## 2. Typography

**Pairing:** display/headings **"Fraunces"** *or* **"Newsreader"** (optional editorial serif for hero H1 & pull quotes to add premium warmth) + UI/body **"Inter"** (or "Geist"). Numerals tabular for prices. Turkish/extended-Latin covered; load Arabic-capable fallback for future RTL.

> Simpler single-family option (recommended for launch): **Inter** everywhere + **Inter Display** for large headings. Add serif accent later.

### Type scale (rem, 1rem=16px) — fluid via `clamp()`
```
Display   clamp(2.75, 5vw, 4.5)rem  / 1.05 / -0.02em / 600
H1        clamp(2.25, 4vw, 3.25)rem / 1.1  / -0.02em / 600
H2        clamp(1.75, 3vw, 2.5)rem  / 1.15 / -0.01em / 600
H3        1.5rem  / 1.25 / 600
H4        1.25rem / 1.3  / 600
Body-lg   1.125rem/ 1.6  / 400
Body      1rem    / 1.6  / 400
Small     0.875rem/ 1.5  / 400
Overline  0.75rem / 1.4  / 600 / uppercase / 0.08em
Price     tabular-nums, 600
```
Max line length 66ch for article body. Generous vertical rhythm (8px base spacing scale).

## 3. Spacing, radius, elevation, motion

```
Spacing scale (px): 2 4 8 12 16 20 24 32 40 48 64 80 96 128  (Tailwind default + 18/22)
Container: max-w-7xl (1280) content; max-w-3xl (768) for article prose
Radius: sm 8 · md 12 · lg 16 · xl 24 · pill 9999   (cards = lg/xl, buttons = md/pill)
Shadow:
  card    0 1px 2px rgba(16,24,40,.06), 0 1px 3px rgba(16,24,40,.10)
  raised  0 8px 24px rgba(16,24,40,.10)
  hero    0 20px 60px rgba(30,58,138,.18)   // brand-tinted
Glass:   bg-white/70 backdrop-blur-xl border-white/40 (sticky bars, hero search only)
```
**Motion:** 150–250ms `ease-out` for hovers, 300–450ms for section reveals (fade+8px rise, IntersectionObserver via Alpine). Respect `prefers-reduced-motion`. No parallax that hurts LCP. Micro-interactions on CTA hover (subtle scale 1.02 + shadow), on verified badge (soft shine once).

## 4. Component library (Livewire/Volt + Blade components)

Organized as `<x-...>` Blade components; interactive ones as Volt/Livewire. Naming grouped by domain.

### Primitives
`x-button` (variants: primary, secondary, ghost, gold, danger; sizes sm/md/lg; loading state) · `x-input` `x-select` `x-textarea` `x-checkbox` `x-radio-card` `x-file-upload` (drag-drop, image/x-ray) · `x-badge` (verified tiers, status) · `x-avatar` · `x-rating-stars` · `x-tag/chip` · `x-tooltip` · `x-alert` · `x-skeleton` · `x-icon` (Lucide/Heroicons set).

### Trust & marketing
`x-verification-badge` (Verified / Verified+ / Elite — with tooltip explaining standard) · `x-trust-bar` (row of guarantees) · `x-stat` (animated count-up) · `x-savings-calculator` · `x-price-range` (from–to, tabular, currency-aware) · `x-testimonial-card` · `x-review-card` (verified-treatment label) · `x-before-after` (slider) · `x-guarantee-seal` · `x-logo-cloud` (press/accreditation).

### Discovery
`x-treatment-card` · `x-clinic-card` (photo, verified tier, rating, price band, languages, key techs, CTA) · `x-doctor-card` · `x-search-bar` (glass hero: treatment + country + budget) · `x-filter-panel` (facets) · `x-compare-tray` (sticky) · `x-map` (clinic pins, lazy-loaded) · `x-city-card` · `x-country-card` (flag).

### Conversion
`x-lead-form` (multi-step Volt component, context-aware) · `x-sticky-cta` · `x-mobile-action-bar` · `x-whatsapp-fab` · `x-exit-intent-modal` · `x-consultation-cta` (repeatable section) · `x-consent-checkbox` (GDPR text + audit).

### Content / SEO
`x-faq-accordion` (emits FAQ schema) · `x-breadcrumbs` (emits BreadcrumbList) · `x-toc` (article) · `x-related-grid` · `x-author-box` (EEAT: reviewer, medically-reviewed-by, dates) · `x-cost-table` · `x-procedure-steps` · `x-prose` wrapper.

### App/dashboard
`x-stat-tile` · `x-data-table` (sortable, Livewire) · `x-lead-row` · `x-kanban-column` (lead status) · `x-timeline` (lead activity) · `x-chat-thread` · `x-offer-card` · `x-invoice-row` · `x-nav-sidebar` · `x-tabs` · `x-modal` · `x-drawer` · `x-toast`.

### States for every component
Default · hover · focus-visible · active · disabled · loading (skeleton/spinner) · error · empty. Documented in a living **Storybook-equivalent** (`/design-system` internal route rendering all components) — build this early.

## 5. Iconography & imagery
- Icons: single consistent set (Lucide), 1.5–2px stroke, `ink-500/700`, brand for active.
- Photography standard (Airbnb-grade): real clinic photos, bright, wide, people-positive; enforced minimum resolution/aspect on upload; AVIF/WebP; never stock that misrepresents a specific clinic.
- Before/After: consent-verified, watermarked source clinic, standardized framing, honest labeling (no retouching beyond neutral color).

## 6. Accessibility baseline (WCAG 2.2 AA)
Focus-visible rings everywhere · semantic landmarks · skip-to-content · labelled form fields + error messaging · 44px min tap targets · color never sole signal (icon+text on badges/status) · reduced-motion · alt text on all clinical imagery · accessible accordions/tabs/modals (aria + keyboard) · form validation announced via `aria-live`.

## 7. Tokens as source of truth
Define once in `tailwind.config.js` + a `resources/css/tokens.css` (CSS custom properties) so tokens are shared by marketing site, app, emails, and PDFs. Export a JSON token file for potential future Figma / mobile parity.
