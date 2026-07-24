# 04 · Wireframes (Mobile-First)

ASCII wireframes describe layout, hierarchy, and conversion intent. Convention: `[ ]` button, `▾` menu, `★` rating, `✓` verified, `▓` image/media, `———` divider. Each page lists **desktop** structure then **mobile** adaptation. Every page repeats the primary CTA and includes breadcrumbs + schema (see [06](06-seo-architecture.md)).

Global chrome on every marketing page:
```
HEADER (sticky, glass on scroll): Logo | Treatments▾ Clinics Doctors Costs Guide Reviews | [Lang▾] [Get Free Quote]
MOBILE HEADER: ☰ | Logo | [WA] [Quote]      BOTTOM BAR (mobile): [ Get Free Quote ]  [ WhatsApp ]
FOOTER: link columns · trust badges · language · legal · social
WhatsApp FAB (bottom-right, all breakpoints)
```

---

## 1. Homepage

```
┌────────────────────────────────────────────────────────────────┐
│ HERO  (brand-900→950 gradient, white text, right: ▓ smiling     │
│        patient / clinic)                                        │
│  Overline: TRUSTED DENTAL CARE IN ISTANBUL                      │
│  H1 (display): "Save up to 70% on world-class dental treatment  │
│                 in Turkey — from verified clinics only."        │
│  Sub: One free request. Matched with vetted clinics. No obligation.
│  ┌──── GLASS SEARCH ────────────────────────────────┐          │
│  │ Treatment ▾ | Country ▾ | Budget ▾ | [ Get Plan ]│          │
│  └──────────────────────────────────────────────────┘          │
│  Trust row: ✓ Verified clinics  ✓ 1,240+ treatments  ★4.9 · GDPR│
└────────────────────────────────────────────────────────────────┘
[ TRUST BAR ]  ISO-standard clinics · Pay after treatment · Free plan in 24h · Mediation guarantee
[ TREATMENT CATEGORIES ]  grid of x-treatment-card (Implants, All-on-4, Hollywood Smile, Veneers, Crowns, Invisalign, Whitening, Emergency) → each links to treatment page
[ HOW IT WORKS ]  3 steps: 1 Tell us your needs → 2 Get matched offers → 3 Fly & smile   [ Start free ]
[ WHY TURKEY ]  savings comparison chart (UK/DE vs TR per treatment) + quality/expertise points
[ WHY CLINICEST ]  4 pillars: Only verified clinics · Transparent pricing · Human+AI concierge · Guarantee
[ FEATURED CLINICS ]  carousel of x-clinic-card (✓ tier, ★, price band, languages)  [ See all clinics ]
[ INTERACTIVE TREATMENT FINDER / AI ADVISOR ]  "Not sure what you need?" → [ Ask the AI Advisor ]
[ PRICE COMPARISON ]  table: treatment | UK | Germany | Turkey(Clinicest) | you save
[ SUCCESS STATISTICS ]  x-stat count-ups: treatments arranged · verified clinics · countries served · avg rating
[ VIDEO SECTION ]  ▓ patient story video (lazy) + short testimonial quotes
[ PATIENT TESTIMONIALS ]  x-testimonial-card row (verified) 
[ FEATURED DOCTORS ]  x-doctor-card row (credentials, cases)  [ Meet the dentists ]
[ CLINIC COMPARISON TEASER ]  "Compare clinics side by side" → [ Open comparison ]
[ FROM THE BLOG ]  3 latest guide posts
[ FAQ ]  x-faq-accordion (6–8 top questions, FAQ schema)
[ BIG LEAD CTA ]  full-width brand band: "Get your free treatment plan today" [ Get Free Quote ] + reassurance
```
**Mobile:** hero stacks (headline → search fields full-width stacked → CTA → trust row); every section single-column; carousels become horizontal snap-scroll; sticky bottom action bar. Above-the-fold priority: headline + search + one trust line. Defer video/map/heavy carousels (lazy).

---

## 2. Treatments hub (`/treatments`)
```
Breadcrumb: Home / Treatments
H1: Dental Treatments in Turkey — transparent prices, verified clinics
Intro (SEO, 120–180 words) + trust bar
[ FILTER/SEARCH treatments ]  chips: Popular · Restorative · Cosmetic · Orthodontic · Emergency
GRID of x-treatment-card: name · from €price · avg savings · ✓clinics count · [Learn more]
[ COST COMPARISON strip ]  → /cost
[ AI ADVISOR CTA ]  [ CONSULTATION CTA ]
Internal links: popular treatments · related guides · countries
```
Mobile: filter chips scroll horizontally; 1-col cards.

---

## 3. Treatment detail (`/treatments/{treatment}`) — the SEO+conversion workhorse
```
Breadcrumb  |  H1: Dental Implants in Turkey — Cost, Procedure & Best Clinics
HERO-LITE: left copy + price band (from €X) + [Get free plan] ; right ▓/before-after
[ QUICK FACTS strip ]: duration · anesthesia · recovery · lifespan · sessions · trips to Turkey
[ STICKY SUB-NAV / TOC ]: Overview · Procedure · Benefits · Risks · Recovery · Cost · Before/After · Clinics · Doctors · FAQ
1  OVERVIEW (what it is, who it's for) — medically-reviewed-by author box (EEAT)
2  PROCEDURE  x-procedure-steps (numbered, visual)
3  BENEFITS  |  4 RISKS & considerations (honest → builds trust + EEAT)
5  RECOVERY & aftercare timeline
6  COST  x-cost-table (Turkey vs UK/DE/US) + factors affecting price + [Get exact quote]
7  BEFORE & AFTER  x-before-after slider gallery (consent-verified)  → /before-after/{treatment}
8  RECOMMENDED CLINICS  x-clinic-card row (filtered to this treatment)
9  RECOMMENDED DOCTORS  x-doctor-card row
10 FAQ (schema)   |   RELATED TREATMENTS grid
[ LEAD CTA block ] repeated mid + end.  Sticky mini-CTA on scroll (mobile bottom bar).
Schema: MedicalProcedure + FAQPage + Breadcrumb + (offers price range)
```
Mobile: TOC collapses to a sticky dropdown; sticky "from €X [Get plan]" bar.

---

## 4. Clinics directory (`/clinics`, `/clinics/{city}`)
```
Breadcrumb | H1: Verified Dental Clinics in Istanbul
[ SEARCH + FILTER PANEL (left, desktop) ] : treatment, price range, verification tier, languages,
   technologies, rating, area/airport distance, guarantee   |   [ Map toggle ]
[ RESULTS ]  sort ▾ (Recommended/Rating/Price) ·  grid/list of x-clinic-card
   each: ▓ photo · Name ✓tier · ★4.9 (128) · from €X · langs · top techs · [View] [+ Compare]
[ MAP (right/toggle, lazy) ] pins
[ COMPARE TRAY sticky ] when items selected → [ Compare (n) ] [ Request quotes ]
SEO footer content for city + internal links (treatments in this city, nearby areas)
```
Mobile: filters in a bottom-sheet drawer; map full-screen toggle; cards 1-col; compare tray as bottom bar.

---

## 5. Clinic profile (`/clinics/{clinic-slug}`)
```
Breadcrumb | HERO: ▓ gallery (photos+video, lightbox) 
Header row: Logo · Name ✓Elite Partner · ★4.9 (128 verified) · city · languages · [Get quote] [WhatsApp] [Save]
[ STICKY SUB-NAV ]: Overview · Treatments&Prices · Doctors · Technologies · Certificates · Reviews · Location · FAQ
Left column (content):
  OVERVIEW (about, years, patients treated, spoken languages)
  TREATMENTS & PRICES  table (treatment | price band | notes)
  DOCTORS  x-doctor-card grid (linked)
  TECHNOLOGIES & FACILITIES  icon list (3D scanner, CBCT, CEREC, sterilization std)
  CERTIFICATES & ACCREDITATIONS  ▓ thumbnails (ISO, ministry license) — verification detail
  BEFORE & AFTER cases
  REVIEWS  summary ★ + AI review summary + verified review list + filters
  LOCATION  map + distance to airport/hotels + transfer note
  FAQ (clinic-specific)
Right column (sticky LEAD CARD):
  "Request a free plan from {Clinic}"  mini-form/CTA · price-match note · response-time badge (<2h)
  [ Get free quote ]  [ WhatsApp ]  [ Book consultation ]
Related: similar clinics · clinics nearby
Schema: MedicalClinic/Dentist + AggregateRating + Review + Breadcrumb + GeoCoordinates
```
Mobile: gallery swipe; sub-nav sticky dropdown; lead card becomes bottom sticky [Get quote][WhatsApp]; sections stacked.

---

## 6. Doctors directory + profile (`/doctors`, `/doctors/{slug}`)
```
DIRECTORY: H1 + filters (treatment specialty, language, clinic, rating) + x-doctor-card grid
PROFILE:
  HERO: ▓ photo · Dr. Name · title/specialty · clinic (link) · languages · ★ · [Request consultation]
  Sticky sub-nav: About · Education · Experience · Certificates · Awards · Cases · Reviews
  ABOUT + credentials (EEAT: verifiable) · EDUCATION timeline · EXPERIENCE years/procedures
  CERTIFICATES & MEMBERSHIPS · AWARDS · BEFORE/AFTER CASES (by treatment) · VIDEOS · PATIENT REVIEWS
  Sidebar CTA: consult with this doctor.  Related doctors.
  Schema: Physician/Dentist + Review + Breadcrumb
```
Mobile: photo + name + CTA first; timelines stack.

---

## 7. Country landing (`/countries/{country}`, e.g. UK)
```
Breadcrumb | H1: Dental Treatment in Turkey for UK Patients
HERO: savings hook (£ vs Turkey) + flag + [Get free quote in GBP]
[ SAVINGS COMPARISON ] table in local currency (GBP) per treatment (You pay UK £X → Turkey £Y, save %)
[ WHY UK PATIENTS CHOOSE TURKEY ] points + testimonials from UK patients
[ TRAVEL INFO ]: Flights (avg time/price from London/Manchester) · Visa (not required / e-visa) ·
                 Currency (GBP↔TRY) · Best time · What to pack
[ THE PROCESS for UK patients ] step-by-step incl. remote consult, aftercare back home
[ TREATMENTS popular with UK patients ] cards → /countries/uk/{treatment}
[ FEATURED CLINICS serving UK patients (English-speaking) ]
[ FAQ (UK-specific: aftercare in UK, guarantees, payment) ] schema
[ LEAD FORM ] pre-set country=UK, currency=GBP
Hreflang cluster with DE/IE/US variants. Schema: FAQ + Breadcrumb + LocalBusiness refs
```
Mobile: currency-localized numbers; travel info as accordions.

---

## 8. Treatment cost (`/cost/{treatment}`, `/cost/{treatment}/{country}`)
```
H1: How Much Do Veneers Cost in Turkey? (2026 Prices)  — updated date shown (freshness/EEAT)
[ PRICE SNAPSHOT ] big number band: Turkey €X–€Y  vs  UK / Germany / USA
[ COST TABLE ] per material/type/count + what's included (consult, x-ray, hotel, transfer)
[ FACTORS AFFECTING PRICE ] list
[ SAVINGS CALCULATOR ] x-savings-calculator (choose treatment+count+country → estimate)
[ IS IT WORTH IT / hidden costs honesty section ]
[ CLINICS offering this ]  [ FAQ ] schema  [ LEAD CTA get exact quote ]
Country variant adds local currency + flights context.  Schema: FAQ + Breadcrumb + priceRange
```

---

## 9. Dental Tourism Guide (pillar `/dental-tourism-turkey` + cluster)
```
PILLAR: H1 mega-guide + sticky TOC + sections (Is it safe? Costs, Choosing a clinic, The trip,
  Recovery, Guarantees, Risks & how to avoid them) each linking to deep cluster articles.
  Author/reviewer box, updated date, downloadable PDF (email micro-conversion), lead CTA.
CLUSTER ARTICLE: standard article template (below), links up to pillar + sideways to siblings.
Schema: Article/MedicalWebPage + FAQ + Breadcrumb + HowTo where relevant
```

---

## 10. Before & After (`/before-after`, `/before-after/{treatment}`)
```
H1 + filter by treatment/clinic  |  masonry grid of x-before-after (slider), each: treatment, clinic✓, consent-verified label
Lightbox with case detail + [See this clinic] [Get similar result — free quote]
Honesty/consent disclaimer. Schema: ImageObject + Breadcrumb
```

---

## 11. Reviews (`/reviews`, `/reviews/{clinic-slug}`)
```
HUB: overall ★, count, AI-summarized themes, filter by treatment/clinic/rating, verified badge on each
CLINIC REVIEWS: clinic header + rating breakdown bars + AI summary + review list (verified-treatment only)
  + "leave a review" (post-treatment, gated).  Schema: Review + AggregateRating
```

---

## 12. Blog (`/blog`, `/blog/{category}`, `/blog/{post}`)
```
INDEX: featured post + category chips + card grid + newsletter capture (micro-conversion)
POST (article template): breadcrumb · H1 · author + medically-reviewed-by + dates · reading time ·
  ▓ hero · TOC · prose (66ch) · inline CTAs · pull quotes · related treatments/clinics · FAQ ·
  related posts · lead CTA.  Schema: Article/BlogPosting + FAQ + Breadcrumb
```

---

## 13. How It Works / About / Contact / FAQ
```
HOW IT WORKS: 4–5 illustrated steps · guarantee & verification standard · trust proof · CTA
ABOUT: mission · verification methodology (credibility) · team · press/partners · stats · CTA
CONTACT: form + WhatsApp + email + office (map) + response-time promise + FAQ link
FAQ: searchable, categorized accordions (Booking, Safety, Costs, Travel, Aftercare, Payments) + FAQ schema + CTA
```

---

## 14. Get a Free Quote (`/get-quote`) — global multi-step funnel
```
Full-screen focused flow, minimal chrome, progress bar, reassurance rail.
Step1 Treatment(s) (radio-cards, pre-filled) → Step2 Photos/x-ray upload (optional) →
Step3 Country + budget + timeline → Step4 Name + email + WhatsApp + [✓ consent].
Success: confirmation + AI cost estimate + 2–3 matched clinic previews + "what happens next" + WhatsApp deep-link.
```
Mobile: one step per screen, big tap targets, upload optional/skippable.

---

## 15. Legal pages (Privacy / Terms / GDPR / Cookies / Medical Disclaimer)
```
Clean single-column prose (max-w-3xl), last-updated date, TOC anchors, contact/DPO info,
cookie preferences link, data-subject request form (GDPR).  Minimal chrome, footer-linked.
```

---

## 16. Patient portal & clinic/admin (see [09](09-crm-admin-architecture.md))
App shell: left sidebar nav + top bar (search, notifications, profile) + content. Data-tables,
kanban lead board, chat threads, offer cards, invoices, analytics tiles. Dark-mode capable.

---

## Responsive rules (all pages)
- Breakpoints: `sm 640 · md 768 · lg 1024 · xl 1280 · 2xl 1536`. Design at 375px first.
- One primary action visible at all times (header button + mobile bottom bar).
- Multi-column → single column below `lg`; sidebars → drawers/bottom-sheets below `md`.
- Tables → stacked cards or horizontal-scroll containers on mobile (never break layout).
- Images `max-w-full`, AVIF/WebP, explicit width/height (no CLS), lazy below fold.
- Sticky elements never cover content or the last CTA; safe-area insets on iOS.
