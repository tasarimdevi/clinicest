# 10 · Delivery Plan & Future Roadmap

## 1. Build phases (MVP → scale)

### Phase 0 — Foundations (weeks 1–3)
Repo, CI/CD, Laravel 12 skeleton, Tailwind + design tokens, `/design-system` gallery, auth (Sanctum/Fortify + roles), base layouts, locale middleware, S3/Cloudflare, Meilisearch, Redis/Horizon, error tracking. Deliver: component library + core primitives + DB migrations for core entities.

### Phase 1 — Lead engine MVP (weeks 3–8) — *revenue-critical*
Homepage, treatment hub + 11 treatment pages, clinic directory + profiles, doctor profiles, **multi-step lead form (`/get-quote`)**, WhatsApp FAB, basic CRM (lead inbox, manual assignment, statuses, notes), transactional email, seed 10–20 verified clinics. **Goal: capture and route real leads.** Full SEO technicals (schema, sitemaps, hreflang scaffold, CWV budget) from day one.

### Phase 2 — Trust & content depth (weeks 8–14)
Reviews (verified model + moderation), before/after galleries, cost pages + calculator, country landing pages (UK/DE/IE), guide pillar + first cluster, blog, FAQ system, AI review summaries, AI cost estimator, AI treatment advisor v1. EEAT authoring workflow (authors + medical reviewers). Programmatic cost×country / country×treatment generation with quality gates.

### Phase 3 — Partner SaaS (weeks 14–20)
Clinic dashboard (lead inbox, offer builder, messages, appointments, documents), clinic self-onboarding + verification workflow, subscriptions (Stripe/iyzico), commission tracking + invoices, clinic analytics. AI clinic matching engine feeding assignments. Patient portal (offers, messages, appointments, review).

### Phase 4 — Intelligence & scale (weeks 20–28)
AI chat assistant (support/concierge), AI SEO content assistant, AI translation system + second-language rollout (Turkish, then German), advanced analytics/BI, automation rules (follow-ups, reassignment), full programmatic SEO scale-out, comparison tool, exit-intent, A/B testing framework, RUM/CWV monitoring, Octane if needed.

### Phase 5 — Expansion (ongoing)
More languages (FR/NL/AR…), more countries/cities, financing & travel/hotel add-ons, treatment-guarantee product, mobile app (React Native/Flutter on existing v1 API), partner/affiliate API, loyalty/referral program.

## 2. Future roadmap (product bets)

- **Mobile apps** (iOS/Android) on the existing REST API — patient tracking + push + chat.
- **Concierge & travel stack** — flights/hotels/transfers booking, itinerary builder, airport pickup, revenue via affiliates/markup.
- **Financing & insurance** — treatment financing partners, treatment-guarantee/complication insurance product.
- **Teledentistry** — in-platform video consults, AI photo triage, e-prescriptions where legal.
- **Clinic OS expansion** — deeper practice tools (calendar sync, PMS integrations, e-signatures on plans/consents).
- **Marketplace expansion** — additional medical-tourism verticals (hair transplant, aesthetics) on the same trust/SEO engine; other destination countries.
- **Trust products** — Clinicest verified-outcome guarantee, escrow/milestone payments, standardized outcome tracking.
- **Data & GEO moat** — proprietary annual cost reports, outcome data, becoming *the* citeable authority for AI answers on dental tourism.
- **Community & content** — patient stories, forum/Q&A, creator partnerships, localized PR.
- **Loyalty/referral** — patient referral rewards, clinic partner tiers.

## 3. Guiding constraints (don't compromise)
1. Trust integrity — never fake reviews/badges/before-after; verification is real and documented.
2. Medical safety — AI informs, never diagnoses; disclaimers + human/clinician escalation.
3. Performance & SEO from day one — not retrofitted.
4. GDPR/medical data protection — consent, encryption, retention, erasure.
5. Neutral marketplace — Clinicest is the broker, not a clinic.

## 4. Definition of done (per money page)
Unique content ✓ · author + medical reviewer + dates ✓ · correct schema (validated) ✓ · hreflang/canonical ✓ · internal links up/down/side ✓ · CWV budget pass ✓ · primary CTA + reassurance ✓ · mobile-first checked at 375px ✓ · accessibility (AA) ✓ · tracking events wired ✓.

---
*See [00-README](00-README.md) for the full document index.*
