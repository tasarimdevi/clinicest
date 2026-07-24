# 02 · Information Architecture, Sitemap & UX Flows

## 1. URL & routing strategy

Language via path prefix, English default served at root (no `/en` duplicate content — canonical to root, hreflang declared). Future languages under prefixes.

```
clinicest.com/                         → EN (default, x-default)
clinicest.com/tr/                      → Turkish
clinicest.com/de/                      → German (secondary phase)
```

### Canonical URL patterns

```
/treatments                                   Treatment hub
/treatments/{treatment}                       e.g. /treatments/dental-implants
/treatments/{treatment}/cost                  Cost sub-page (pSEO)
/clinics                                       Clinic directory (search/filter)
/clinics/{city}                               City directory  e.g. /clinics/istanbul
/clinics/{clinic-slug}                         Clinic profile
/doctors                                       Doctor directory
/doctors/{doctor-slug}                         Doctor profile
/dental-tourism-turkey                         Pillar guide
/dental-tourism-turkey/{topic}                 Guide cluster articles
/countries/{country}                          e.g. /countries/uk  (country landing)
/countries/{country}/{treatment}              e.g. /countries/uk/dental-implants (pSEO)
/cost/{treatment}                             Cost hub  e.g. /cost/veneers
/cost/{treatment}/{country}                   e.g. /cost/veneers/germany (pSEO)
/before-after                                  Gallery hub
/before-after/{treatment}                     Filtered gallery
/reviews                                       Reviews hub
/reviews/{clinic-slug}                         Clinic reviews
/compare?clinics=a,b,c                         Comparison tool
/blog  /blog/{category}  /blog/{post}
/about  /contact  /faq  /how-it-works
/legal/privacy  /legal/terms  /legal/gdpr  /legal/cookies  /legal/medical-disclaimer
/get-quote                                     Global lead funnel (multi-step)
```

> **Programmatic surfaces** (see [06](06-seo-architecture.md)): `treatments × cities`, `cost × country`, `country × treatment`, `treatment × clinic`. Every generated page must clear a *quality gate* (unique data, min content, real entities) before indexing — no thin doorway pages.

## 2. Top-level sitemap

```
Clinicest.com
├── Home
├── Treatments (hub)
│   ├── Dental Implants ├─ /cost ├─ before-after ├─ FAQ
│   ├── All-on-4 · All-on-6
│   ├── Hollywood Smile · Veneers · Crowns · Bridges
│   ├── Invisalign · Teeth Whitening · Root Canal
│   └── Dental Emergency
├── Clinics (directory + filters + map)
│   ├── By city (Istanbul, …)
│   └── Clinic profile → Doctors, Reviews, Gallery, Prices, Map, Lead form
├── Doctors (directory) → Doctor profile
├── Dental Tourism Guide (pillar) → topic cluster
├── Countries → UK / Germany / Ireland / USA / … → (country × treatment)
├── Treatment Cost (hub) → per-treatment → per-country
├── Before & After (hub) → per-treatment
├── Reviews (hub) → per-clinic
├── Blog → categories → posts
├── How It Works
├── About · Contact · FAQ
├── Legal: Privacy · Terms · GDPR · Cookies · Medical Disclaimer
├── Get a Free Quote (global multi-step funnel)
└── Auth: Patient login/register · Clinic portal · Admin
```

## 3. Global navigation

**Primary header (desktop):** Logo · Treatments ▾ (mega-menu) · Clinics · Doctors · Costs · Guide · Reviews · [Language ▾] · [Get Free Quote — primary button].

**Mega-menu (Treatments):** three columns — *Popular* (Implants, All-on-4, Hollywood Smile, Veneers), *Restorative* (Crowns, Bridges, Root Canal), *Cosmetic/Ortho* (Whitening, Invisalign) — plus a promo card ("Free treatment plan in 24h").

**Mobile header:** Logo · [WhatsApp icon] · [Get Quote] · hamburger. Sticky. Bottom sticky bar: `Free Quote` + `WhatsApp`.

**Footer:** Treatments · Popular cities · Countries · Company (About, How it works, Careers) · Trust (Verification standard, Guarantee, Reviews) · Legal · Language · trust badges (GDPR, ISO clinics, payment/mediation) · social.

## 4. Primary UX flow — patient to lead (the money path)

```
Discovery (SEO/ad/referral)
   → Landing page (treatment / country / clinic / cost / blog)
      → Trust absorption (badges, reviews, savings, verification)
         → Micro-conversion (see price / AI advisor / compare / save)
            → Lead form: "Get a Free Treatment Plan"
               Step 1 Treatment(s) + issue  (pre-filled from context)
               Step 2 Photos/x-ray upload (optional, boosts quality)
               Step 3 Country, budget, timeline
               Step 4 Contact (name, email, WhatsApp)  → CONSENT (GDPR)
            → Instant confirmation + AI cost estimate + "what happens next"
   → Backend: lead scored → matched to clinics → assigned
      → Clinic responds with plan/offer (SLA < 2h/24h)
         → Patient reviews offers in patient portal / via email+WhatsApp
            → Books consultation / accepts → travels → treatment
               → Completion marked → commission invoiced → review requested
```

### Lead form design rules
- **Progressive disclosure**, 4 short steps, one decision per screen, progress bar, ~30–60s to complete.
- **Context pre-fill:** entering from `/treatments/veneers` pre-selects Veneers; from `/countries/uk` pre-sets country UK + GBP.
- **No dead ends:** every field optional except contact + consent; partial submits still captured (progressive lead capture).
- **Reassurance rail:** "Free · No obligation · 100% confidential · Reply within 24h" persistent beside the form.
- **Instant value:** end with an AI cost estimate + matched clinic previews so the reward is immediate, not "we'll email you."

## 5. Secondary flows

**AI Treatment Advisor:** conversational — "Tell us your situation" → clarifying Qs → recommended treatment(s) + cost band + 3 matched clinics → CTA to full quote. Feeds the same lead pipeline.

**Compare clinics:** add up to 3–4 clinics to a comparison tray → side-by-side (price band, verification tier, languages, techs, rating, distance from airport) → single CTA "Request quotes from selected."

**Clinic self-onboarding:** apply → verification workflow (docs upload, credential check) → profile builder → go-live after admin approval → subscription selection.

**Patient portal:** track request status, view/compare clinic offers, message clinics, upload documents, manage appointments, leave review post-treatment.

## 6. Conversion optimization system

| Mechanism | Rule (honest-urgency policy) |
|-----------|------------------------------|
| Sticky primary CTA | Always reachable; header button + mobile bottom bar |
| Floating WhatsApp | Persistent, pre-filled context message |
| Exit-intent | Desktop only, once/session, offers AI estimate or guide PDF — value, not guilt |
| Social proof | Real counts ("1,240 treatments arranged"), verified reviews, live-but-true activity only |
| Trust badges | Verification tier, GDPR, guarantee, secure — repeated near every CTA |
| Micro-conversions | See price, save clinic, download guide, start advisor — each captures intent/email |
| Scarcity | Only if literally true (e.g. real limited consultation slots); otherwise none |
| Multi-channel follow | Email automation + WhatsApp + optional SMS after consented lead |

**Accessibility & performance are conversion features:** WCAG 2.2 AA, keyboard flows, fast LCP (<2.0s), no layout shift on the form.

## 7. Empty/edge states
No matching clinics → widen criteria + human concierge offer. Slow clinic response → auto-reassign. Failed upload → skip-and-continue. Unsupported country → still capture, route to concierge. Never a hard dead-end before contact is captured.
