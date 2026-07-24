# 06 · SEO Architecture

Goal: dominate dental-tourism search worldwide via topic authority, programmatic scale, EEAT-grade medical content, flawless technicals, and GEO (generative-engine) readiness. Capacity target: 500+ blog articles, 300+ treatment/cost pages, 200+ clinic pages, 600+ doctor pages, plus country/city/comparison pages.

## 1. Topic cluster model (semantic authority)

Pillars own head terms; clusters capture long-tail and link up.

```
PILLAR: Dental Tourism Turkey (/dental-tourism-turkey)
  ├─ clusters: safety, choosing a clinic, costs overview, the trip, guarantees, risks, aftercare-at-home
PILLAR (per treatment): Dental Implants in Turkey (/treatments/dental-implants)
  ├─ /treatments/dental-implants/cost
  ├─ /cost/dental-implants  +  /cost/dental-implants/{country}
  ├─ /before-after/dental-implants
  ├─ blog clusters: "implants vs bridges", "how long do implants last", "all-on-4 vs implants" ...
  └─ entity links: clinics offering implants, doctors performing implants
PILLAR (per country): Dental Treatment Turkey for UK Patients (/countries/uk)
  ├─ /countries/uk/{treatment}  (implants, veneers, all-on-4 …)
  └─ blog: "flying to Istanbul for dental work from the UK", "NHS vs Turkey costs"
```

Every cluster page links **up** to its pillar, **sideways** to 2–4 siblings, and **down/out** to entities (clinics, doctors). Pillars link **down** to all clusters. This is enforced by an automated **internal-linking service** (see §6).

## 2. Programmatic SEO (pSEO) matrices

Generated pages, each gated by a **quality threshold** (unique data + min words + ≥1 real clinic/doctor + human-review flag for money pages) to avoid thin/doorway penalties.

| Matrix | Pattern | Volume driver | Unique data source |
|--------|---------|---------------|--------------------|
| Cost × Country | `/cost/{treatment}/{country}` | treatments × target countries | local price data, currency, flights, savings % |
| Country × Treatment | `/countries/{country}/{treatment}` | same | localized process, testimonials, clinics serving that country |
| Treatment × City | `/clinics/{city}` + treatment facet | cities × treatments | real clinic inventory in city |
| Clinic pages | `/clinics/{slug}` | onboarded supply | genuine profile, reviews, prices |
| Doctor pages | `/doctors/{slug}` | supply | genuine credentials, cases |
| Comparison | `/compare`, "X vs Y" blog | treatment/clinic pairs | structured comparison data |

**Guardrails:** no page indexed until it passes the gate; `noindex` until enough real content; canonical to the strongest variant; consolidate near-duplicates; monitor Search Console for soft-404/thin flags.

## 3. EEAT implementation (medical YMYL)

Dental content is **Your-Money-Your-Life** → Google demands high trust. Build it in structurally:

- **Experience & Expertise:** every treatment/cost/guide page has an **author** and a **"Medically reviewed by Dr. …"** with real credentials, linked doctor profile, and **reviewed/updated dates**.
- **Authoritativeness:** cite reputable sources (peer-reviewed, dental associations), link out where appropriate, build backlinks/PR, show accreditations.
- **Trustworthiness:** transparent pricing, honest risk sections, real verified reviews, clear About/verification methodology, contact + physical presence, secure site, clear editorial & medical-review policy pages.
- **Content quality:** original data (price research), real photos, unique per-page value — never spun/duplicated. AI *assists* drafting but every YMYL page is human-edited and expert-reviewed before publish (workflow in [07](07-ai-architecture.md)).

## 4. Structured data (Schema.org / JSON-LD)

Rendered via a `SchemaService` (see [08](08-laravel-architecture.md)), one graph per page, validated in CI.

| Page type | Schema types |
|-----------|--------------|
| Global (all) | `Organization` + `WebSite` (with `SearchAction`), `BreadcrumbList` |
| Treatment | `MedicalProcedure` / `MedicalWebPage`, `FAQPage`, price via `offers`/`priceRange` |
| Cost | `FAQPage`, `Table`(described), price info, `BreadcrumbList` |
| Clinic | `MedicalClinic` / `Dentist` (`LocalBusiness`), `AggregateRating`, `Review`, `GeoCoordinates`, `openingHours` |
| Doctor | `Physician`/`Dentist`, `AggregateRating`, `Review`, `alumniOf`, `award` |
| Review pages | `Review`, `AggregateRating` |
| Blog/guide | `Article`/`BlogPosting` or `MedicalWebPage`, `author`, `reviewedBy`, `datePublished/Modified`, `FAQPage`, `HowTo` where stepwise |
| Country | `FAQPage`, `BreadcrumbList`, references to clinics |
| Before/After | `ImageObject` (with honest caption/consent) |

Policy: only mark up content actually visible on the page; never fake `AggregateRating`. Review markup only from verified reviews.

## 5. Internationalization SEO (hreflang / canonical)

- Default EN at root = `x-default` and `en`. Additional locales under path prefixes.
- Every translatable entity emits **reciprocal hreflang** tags for all available locales + `x-default`. Missing translations are omitted (never point hreflang at an untranslated page).
- **Self-referencing canonical** per locale; cross-locale pages are alternates, not duplicates.
- Country pages target **markets** (UK/US/IE share `en` but differ by content/currency) — differentiate with `en-GB`, `en-US`, `en-IE` hreflang + genuinely localized content (currency, flights, testimonials) so they aren't dupes.
- Currency/units localized by market; language by locale — kept independent.

## 6. Internal linking engine

Automated + editorial:
- **Automated contextual links:** a service maps entities (treatment↔clinic↔doctor↔city↔country) and injects contextual links (recommended clinics on treatment pages, treatments on clinic pages, sibling cost/country links) with sensible caps and relevance ranking.
- **Breadcrumbs** on every page (also schema).
- **Related grids** (related treatments/posts/clinics) computed by shared taxonomy + embeddings similarity.
- **Hub → spoke** enforced from pillar config; **spoke → hub** always present.
- **Anchor-text variety** managed to stay natural; avoid over-optimization.
- Orphan-page detector in admin SEO panel flags pages with < N internal inlinks.

## 7. Technical SEO checklist

- **Core Web Vitals** first (see [08](08-laravel-architecture.md) performance): LCP < 2.0s, INP < 200ms, CLS < 0.1. This is both ranking + conversion.
- Clean semantic HTML, one H1, logical heading order, descriptive `alt`.
- XML sitemaps: split by type (`sitemap-treatments.xml`, `-clinics`, `-doctors`, `-posts`, `-countries`, `-cost`) + sitemap index; auto-updated; `lastmod` accurate. News/image sitemaps where relevant.
- `robots.txt` + per-page robots via `seo_meta`. Faceted/search URLs `noindex,follow` or canonicalized to avoid crawl bloat; parameter handling defined.
- 301 redirect manager (`redirects` table) for slug changes; no broken links (CI link-checker).
- Canonical on paginated/filtered listings; `rel=prev/next` semantics via self-canonical + crawlable pagination.
- SSR HTML (Livewire renders server-side) → fully crawlable without JS; no client-only content for SEO-critical text.
- Fast, cached, CDN (Cloudflare) edge delivery; image CDN with AVIF/WebP + responsive `srcset`.
- Structured breadcrumb + descriptive, keyworded, human-readable slugs (no IDs).
- Freshness: `datePublished`/`dateModified` surfaced; scheduled content refresh workflow for price pages (prices change yearly → "2026 prices").

## 8. GEO / AI-search readiness (generative engines)

Optimize to be **cited by** ChatGPT/Gemini/Perplexity/AI Overviews:
- **Answer-first structure:** clear question headings (H2/H3 as questions), concise direct answers up top, then depth — extractable Q→A blocks.
- **Definitive, well-structured facts:** price tables, comparison tables, bullet summaries, TL;DR boxes → easy for LLMs to lift and attribute.
- **Rich schema + clean semantics** help machine parsing.
- **Entity clarity:** consistent naming of treatments/clinics/doctors (sameAs to authoritative profiles), `Organization` knowledge-graph signals.
- **`llms.txt`** at root summarizing the site + key canonical URLs for AI crawlers; allow reputable AI crawlers in robots policy where beneficial.
- **Statistics & original data** (cost studies) are highly citeable → publish and update annually.
- **FAQ/HowTo schema** doubles as AI-answer fuel.
- Track AI-referral traffic and citations as a distinct channel.

## 9. Keyword → page mapping (governance)

A `keyword_map` (managed in admin SEO module) assigns each **primary keyword** to exactly one canonical page to prevent cannibalization; secondary keywords cluster beneath. New content briefs are generated against gaps (see AI SEO assistant in [07](07-ai-architecture.md)). Rank tracking + Search Console + GA4/GSC API feed the SEO dashboard.

## 10. Measurement
GSC + GA4 (or privacy-friendly analytics) + server logs. KPIs: indexed pages vs published, non-brand organic sessions, ranking keywords by cluster, CTR by template, lead CVR by landing template, AI-citation share, CWV pass rate. Weekly SEO health report auto-generated in admin.
