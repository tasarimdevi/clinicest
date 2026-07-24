# 01 · Product Strategy

## 1. Positioning statement

For international patients considering dental treatment abroad, **Clinicest** is the independent, verification-first marketplace that removes the fear of choosing a clinic in Turkey — by curating only vetted private clinics, showing transparent pricing and outcomes, and guiding patients end-to-end. Unlike a single clinic's website or a lead-farm directory, Clinicest is a neutral broker whose incentives are aligned with patient outcomes: we only earn when treatment succeeds.

## 2. The problem

| Patient pain | Current "solution" | Why it fails |
|--------------|--------------------|--------------|
| "Is this clinic safe / real?" | Facebook groups, random directories | No verification, fake reviews, no accountability |
| "How much will it *really* cost?" | Clinic sites hide prices | Opaque, bait pricing, surprise upsells |
| "Which clinic fits *my* case?" | DIY across 40 tabs | No structured matching, decision paralysis |
| "What if it goes wrong abroad?" | Nothing | No mediator, no guarantee, no recourse |
| "How do I organize travel/hotel/transfers?" | Fragmented | High friction, drop-off |

Clinicest resolves each: **verification → transparent pricing → AI-assisted matching → mediation & guarantee → concierge**.

## 3. Business model

Three stacked revenue streams:

1. **Success commission (primary).** Partner clinics pay a % commission on completed treatments sourced through Clinicest. Tracked from lead → assignment → treatment → invoice. Typical 10–20% depending on treatment value and clinic tier.
2. **Premium clinic subscriptions (recurring).** Tiered SaaS memberships (e.g. Verified / Growth / Elite) that unlock better placement eligibility, richer profiles, more lead volume, analytics, and priority support. Ranking is never *purely* pay-to-win — quality signals dominate — but subscription unlocks visibility surfaces.
3. **Value-add services (expansion).** Travel/hotel/transfer affiliate revenue, financing partners, treatment-guarantee insurance product, featured placements, sponsored content.

**Unit economics logic:** high average treatment value (implants, All-on-4/6, Hollywood Smile run €2k–€15k) means even a mid-single-digit commission yields strong revenue per completed lead. The moat is trust + SEO + supply quality, not price.

## 4. Value propositions by stakeholder

**Patients** — Safety (only verified clinics), savings (transparent 50–70% vs UK/DE), simplicity (one request, matched options), support (human + AI concierge, mediation if issues).

**Clinics** — Qualified international demand without ad spend, pay-on-success model (low risk), CRM + dashboard tooling, brand credibility from association, analytics.

**Clinicest** — Compounding SEO authority, two-sided network effects, recurring + performance revenue, defensible verified-supply moat.

## 5. Trust architecture (the differentiator)

Trust is engineered as explicit, provable signals — never claimed, always shown:

- **Verification badges** with a defined, published standard (license check, sterilization/ISO, dentist credentials, on-site or documented audit). Badge states: `Verified`, `Verified+`, `Elite Partner`.
- **Transparent pricing** — price ranges shown up front; "no surprise pricing" pledge.
- **Real reviews only** — reviews tied to actual completed treatments (verified-stay model like Booking), moderated, AI-summarized but never AI-fabricated.
- **Clinicest Guarantee / mediation** — defined recourse if treatment deviates from plan.
- **Doctor-level transparency** — named dentists, credentials, case galleries.
- **Data protection** — GDPR-first, medical-data handling, consent logs.

> Non-negotiable: no fake reviews, no fake testimonials, no fabricated before/after, no manipulative urgency ("2 people looking now" only if literally true). Trust broken once = business dead.

## 6. Target market & prioritization

| Tier | Countries | Rationale | Localisation priority |
|------|-----------|-----------|-----------------------|
| Primary | UK, Germany, Ireland | High dental costs, established Turkey-travel behavior, English/German reach | EN + DE, country landing pages first |
| Secondary | USA, Canada, Australia | High cost, English, high treatment value | EN, currency/flight variants |
| Future | France, Netherlands, Belgium, Sweden, Saudi Arabia, UAE | Expansion; FR/NL/AR later | Unlimited-language architecture ready |

Default language **English**; second **Turkish** (clinic-side + domestic). Architecture supports unlimited languages via translatable content + hreflang (see [06](06-seo-architecture.md), [08](08-laravel-architecture.md)).

## 7. Competitive frame

| Benchmark | What we borrow |
|-----------|----------------|
| Booking.com | Verified inventory, review-after-stay integrity, comparison UX, urgency-with-honesty, filters |
| Airbnb | Trust design, host (clinic) profiles, photography standards, wishlists, clean discovery |
| Healthgrades | Doctor authority, medical EEAT, structured clinical content, condition→treatment→provider funnel |
| Stripe / Linear | Product/marketing polish, spacing, typographic scale, calm premium aesthetic |

## 8. Success metrics (North Star + supporting)

- **North Star:** number of *completed* treatments sourced per month (revenue-and-value aligned).
- Funnel: organic sessions → consultation requests (lead CVR ≥ 4–6% on treatment/country pages) → qualified leads → clinic assignments → treatments completed.
- SEO: indexed pages, ranking keywords, non-brand organic share, topic-cluster coverage.
- Supply: verified clinics onboarded, active clinics, avg response time to lead (< 2h target).
- Retention: clinic subscription retention, patient NPS, review submission rate.

## 9. Roles overview (detailed in [09](09-crm-admin-architecture.md))

`Guest` · `Patient (lead)` · `Registered Patient` · `Clinic Owner` · `Clinic Staff` · `Doctor` · `Content Editor` · `SEO Manager` · `Sales/CRM Agent` · `Finance` · `Moderator` · `Admin` · `Super Admin`. Enforced via Spatie roles + granular permissions.
