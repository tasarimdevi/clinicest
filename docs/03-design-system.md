# 03 · UI Design System

**Design language:** *The Travel Dossier* — Clinicest is dental tourism, so the identity leans into that literally: a boarding-pass/departure-board vocabulary (route codes, ticket stubs, manifest grids) laid over a "Trusted Medical Luxury" foundation — Bosphorus navy, clinical porcelain, antique brass, verification teal. Calm and confident, not sterile; premium, not kitschy — the travel motif shows up in structure (ticket die-cuts, tabular flight-style numbers) rather than literal plane icons.

> A working visual reference for this identity lives as a published artifact (homepage prototype) — see the design plan below for the canonical values, and `resources/css/app.css` for the implemented Tailwind v4 tokens.

## 1. Color palette

Bosphorus-navy + clinical porcelain base, antique-brass for premium/verification accents, a dedicated clinical teal for "verified"/positive figures — kept separate from brass so the two accents never compete. Reds/greens reserved for status only.

> **Implementation note:** the scaffold uses **Tailwind v4** (Laravel 12 default), which is CSS-first — tokens live in `resources/css/app.css` under `@theme { --color-brand-600: ... }` rather than `tailwind.config.js`. The block below mirrors that file exactly.

### Tokens (mirrors `resources/css/app.css` `@theme` block)

```
colors: {
  brand: {                 // vivid royal blue — 950/900 double as the hero/header ground
    50:'#EEF0FB', 100:'#DDE0F8', 200:'#BCC2F1', 300:'#929CE8',
    400:'#6372DE', 500:'#3548D4', 600:'#2637B5', 700:'#1F2D93',
    800:'#1A257A', 900:'#161F67', 950:'#131C5C',
  },
  gold: {                  // antique brass — verification/premium accent (use sparingly)
    50:'#FBF3E7', 100:'#F5E4C7', 300:'#E3BE87',
    400:'#C79B57', 500:'#A97833', 600:'#8F6529', 700:'#6E4D1E',
  },
  teal: {                  // clinical verification / positive-savings accent
    50:'#E9F5F2', 100:'#CDE9E3', 300:'#7CBFAF',
    400:'#3E9683', 500:'#14675C', 600:'#0F5049',
  },
  ink: {                   // neutral text/surfaces — blue-grey, hue-biased toward brand
    50:'#F4F6F7', 100:'#E7EBEE', 200:'#D3DBE1', 300:'#B3BFC9', 400:'#8493A5',
    500:'#66768B', 600:'#4E5C70', 700:'#3A4557', 800:'#253046', 900:'#17263F', 950:'#0E1526',
  },
  success:{500:'#12A150', 600:'#0E8A44'},   // verified / positive — distinct from teal accent
  warning:{500:'#C98A2E'},
  danger: {500:'#B3413A', 600:'#93332D'},   // emergency / errors only
}
```

### Usage rules
- **Primary action / links:** `brand-600`. Hover `brand-700`. Focus ring `brand-500/35`.
- **Header & hero:** `brand-950` ground (a *deliberately* always-dark zone — it does not flip with light/dark mode, see below), `ink-50` text at ~90% opacity for muted copy.
- **Gold:** only for verification tier ("Elite Partner"), premium badges, ticket/boarding-pass accents. Never for body buttons or running text.
- **Teal:** "verified" checkmarks and positive savings figures only — kept semantically separate from gold so premium ≠ verified are visually distinct claims.
- **Backgrounds:** `ink-50` (porcelain) page ground / white cards with `ink-200` border + `shadow-card`.
- **Status:** green = verified/positive, amber = pending, red = emergency/error only.
- **Contrast:** all text ≥ WCAG AA (body ≥ 4.5:1; large text ≥ 3:1). Any accent placed *inside* the always-dark hero/header band must be checked against that fixed dark ground, not against the current theme's swapped token — see Dark mode below.

### Dark mode
Full support, not just "optional for app screens" — tokens are redefined under `@media (prefers-color-scheme: dark)` and mirrored under explicit `:root[data-theme="dark"]` / `[data-theme="light"]` overrides so the in-app theme toggle wins in both directions. The hero/header band is an intentional exception: it's `brand-950` in both themes (a single-toned zone, like the artifact's boarding-pass motif), so any gold/teal accent placed inside it must use a fixed, non-theme-swapped value calibrated against that constant dark ground — never the plain `brand-600`/`gold-500`/`teal-500` tokens, which are calibrated for whichever background the *current* theme puts them on.

## 2. Typography

**Pairing (three roles, each earning its place):**
- **Display serif** — `font-serif` → `"Iowan Old Style", "Palatino Linotype", "URW Palladio L", "Book Antiqua", Georgia, serif`. Headlines, pull-quotes. Warm, editorial, a nod to travel-document typography — used with restraint, never for body copy.
- **Body sans** — `font-sans` (Tailwind default) → the OS-native stack (`-apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial`). Running copy; legible, not an imported "safe" face.
- **Utility mono** — `font-mono` → `"SF Mono", "Cascadia Code", Consolas, "Courier New", monospace`. Ticket/manifest codes, prices, savings-ledger figures — always paired with `tabular-nums`.

Numerals tabular for prices throughout. Turkish/extended-Latin covered by all three stacks; load an Arabic-capable fallback before future RTL work.

### Type scale (rem, 1rem=16px) — fluid via `clamp()`
```
Display   clamp(2.3, 4.6vw, 3.6)rem   / 1.06 / -0.01em / 500 / font-serif
H1        clamp(2.25, 4vw, 3.25)rem   / 1.1  / -0.02em / 600 / font-sans
H2        clamp(1.7, 3vw, 2.35)rem    / 1.15 / -0.01em / 500 / font-serif
H3        1.5rem  / 1.25 / 600 / font-sans
H4        1.25rem / 1.3  / 600 / font-sans
Body-lg   1.125rem/ 1.6  / 400 / font-sans
Body      1rem    / 1.6  / 400 / font-sans
Small     0.875rem/ 1.5  / 400 / font-sans
Overline  0.72rem / 1.4  / 600 / uppercase / 0.14em / font-mono
Price     tabular-nums, 600, font-mono
```
Max line length 66ch for article body. Generous vertical rhythm (8px base spacing scale).

## 3. Spacing, radius, elevation, motion

```
Spacing scale (px): 2 4 8 12 16 20 24 32 40 48 64 80 96 128  (Tailwind default + 18/22)
Container: max-w-7xl (1280) content; max-w-3xl (768) for article prose
Radius: sm 6 · md 10 · lg 14 · xl 20 · pill 9999   (cards = lg/xl, buttons = md/pill)
Shadow:
  card    0 1px 2px rgba(11,30,58,.06), 0 6px 20px rgba(11,30,58,.08)
  raised  0 8px 24px rgba(11,30,58,.12)
  hero    0 30px 80px rgba(6,16,32,.35)   // navy-tinted, for elements lifted off the hero band
Glass:   header sticks over the hero with a translucent brand-950 wash + backdrop-blur (not white — the header/hero band stays dark always, see §1 Dark mode)
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
