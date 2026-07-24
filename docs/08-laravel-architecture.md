# 08 · Laravel Architecture, API, Security & Performance

Laravel 12 · PHP 8.4 · Livewire 3 + Volt · Tailwind · AlpineJS · MySQL 8 · Redis · Meilisearch · Cloudflare · S3. Modular monolith (domain-oriented), not microservices — one deployable app, clean internal boundaries.

## 1. Architectural style

**Domain-oriented modular monolith.** Business logic lives in **Actions/Services** (not controllers, not models). Livewire components are thin (presentation + orchestration). A versioned REST API shares the same domain layer as the web app.

Layers:
```
HTTP (Livewire components, Controllers for API/webhooks)
  → Application (Actions, Services, DTOs, Form Requests, Policies)
    → Domain (Models, Enums, Value Objects, Events, domain services)
      → Infrastructure (Repositories/queries, external clients: AI, WhatsApp, Stripe/iyzico, S3, Meili)
```

Key patterns: **Action classes** (single `handle()`), **Service classes** for cross-cutting (SeoService, SchemaService, MatchingService, AiService, CommissionService), **Events + Listeners** for side-effects (LeadCreated → notify, assign, email), **Jobs** for async, **Policies** for authorization, **Form Requests** for validation, **API Resources** for serialization, **Enums** (PHP 8.4 backed enums) for statuses.

## 2. Folder structure

```
app/
├── Actions/                      # single-purpose use cases
│   ├── Leads/{CreateLead, ScoreLead, AssignLeadToClinics, ConvertLeadToCase}.php
│   ├── Clinics/{VerifyClinic, PublishClinic, UpdateClinicPricing}.php
│   ├── Offers/{CreateOffer, AcceptOffer}.php
│   ├── Commissions/{GenerateCommission, InvoiceCommission}.php
│   └── Content/{PublishPost, GenerateSitemap}.php
├── Ai/
│   ├── AiService.php  Contracts/  Providers/{ClaudeProvider}.php
│   ├── Prompts/       Features/{TreatmentAdvisor, ClinicMatcher, CostEstimator,
│   │                            ReviewSummarizer, FaqGenerator, SeoAssistant, Translator}.php
│   ├── Rag/{Embedder, Retriever}.php  Tools/{GetPrice, FindClinics}.php
│   └── Guardrails/
├── Domain/                       # optional: group models/enums/VOs by aggregate
│   ├── Leads/  Clinics/  Doctors/  Treatments/  Billing/  Reviews/  Content/  Geo/
├── Models/                       # Eloquent (or under Domain/*)
├── Enums/{LeadStatus, VerificationTier, OfferStatus, CommissionStatus, SubscriptionStatus}.php
├── Events/  Listeners/  Jobs/    # async + side effects
├── Livewire/
│   ├── Public/{HomePage, TreatmentPage, ClinicProfile, DoctorProfile, ClinicDirectory,
│   │           CountryPage, CostPage, LeadForm, AiAdvisor, CompareTray, ReviewsPage}.php
│   ├── Patient/{Dashboard, RequestStatus, Offers, Messages, Appointments, LeaveReview}.php
│   ├── Clinic/{Dashboard, LeadInbox, OfferBuilder, Messages, Appointments, Documents,
│   │           TreatmentPlans, CommissionReports, Subscription, ProfileEditor}.php
│   └── Admin/{Dashboard, Clinics, Doctors, Leads(CRM), Treatments, Countries, Posts,
│               Reviews, Seo, Media, Users, Roles, Payments, Commissions, Invoices, Settings}.php
│   └── Volt/                     # Volt single-file components (forms, small widgets)
├── Http/
│   ├── Controllers/Api/V1/       # REST controllers
│   ├── Controllers/Webhooks/{StripeController, WhatsappController}.php
│   ├── Middleware/{SetLocale, EnsureClinicMember, TrackUtm, SecurityHeaders}.php
│   ├── Requests/                 # Form Requests (validation)
│   └── Resources/                # API Resources / JSON transformers
├── Policies/
├── Services/{SeoService, SchemaService, SitemapService, InternalLinkService,
│             MatchingService, CommissionService, WhatsappService, PaymentService,
│             MediaService, TranslationService, PricingService, AnalyticsService}.php
├── Support/  Providers/  Console/Commands/
resources/
├── views/  components/           # Blade x-components (design system)
│   ├── layouts/{public, app, auth, print}.blade.php
│   ├── components/{button, clinic-card, lead-form, verification-badge, faq-accordion,
│   │               breadcrumbs, before-after, ...}.blade.php
│   └── livewire/  emails/  pdf/
├── css/{app.css, tokens.css}  js/{app.js, alpine/}
├── lang/{en, tr, de}/            # UI strings per locale
routes/
├── web.php  api.php  channels.php  console.php
│   # web split via includes: routes/web/{public, patient, clinic, admin, auth}.php
database/{migrations, factories, seeders}
config/{ai.php, seo.php, clinicest.php, scout.php, permission.php}
tests/{Unit, Feature, Browser(Dusk/Pest)}
```

## 3. Multi-language architecture
- **Locale resolution middleware** `SetLocale`: path prefix → session → `Accept-Language` → default `en`. Locale set on app + Carbon + number formatting.
- **UI strings:** `lang/{locale}` files. **Content:** translatable models (see [05](05-database-schema-erd.md)).
- **Localized routing:** route names stable; localized slugs supported per entity (`slug` per locale). URL generator resolves per active locale; hreflang built from available translations.
- **Currency vs language independent:** `MarketService` derives currency/flight context from country; language from locale. Country landing pages combine both.
- Adding a language = add locale to config + lang files + trigger `Translator` jobs; no code changes for new content languages.

## 4. API architecture (REST, versioned)

- **`/api/v1`**, JSON:API-ish resources, token auth via **Laravel Sanctum** (SPA/mobile) + personal access tokens for clinic integrations; OAuth-style scopes per role.
- **Public read endpoints:** treatments, clinics, doctors, reviews (cached, rate-limited) — powers future mobile app + partners.
- **Patient endpoints:** leads (create), offers, messages, appointments, reviews (auth scoped to owner).
- **Clinic endpoints:** lead inbox, offers, messages, documents, commissions, subscription (scoped to clinic membership).
- **Admin endpoints:** management resources (scoped by permission).
- **Webhooks (incoming):** Stripe/iyzico (payments/subscriptions), WhatsApp Business API (inbound messages), verified via signatures, idempotent (`webhooks_incoming`).
- **Conventions:** API Resources for output, Form Requests for input, cursor pagination for large lists, consistent error envelope, `Idempotency-Key` on mutating endpoints, ETags/caching on public reads, rate limiting per token/IP, OpenAPI spec generated + published for partners/mobile.
- **Future mobile API:** same v1, Sanctum tokens, push via FCM; designed now, no rework needed.

## 5. Security architecture

- **AuthN:** Laravel Sanctum + Fortify; strong password policy, **2FA** for clinic/admin, email verification, throttled logins, device/session management.
- **AuthZ:** **Spatie Permission** roles + granular permissions; **Policies** on every model; clinic-scoped access via `EnsureClinicMember` (users only see their clinic's leads/offers). Admin actions permission-gated + audited.
- **Data protection (GDPR/medical):** encryption at rest (DB sensitive fields via Laravel encrypted casts; S3 SSE; app-level encryption for x-rays/medical images), TLS everywhere, consent capture + `consents`/`audit_logs`, data-subject access/erasure jobs, data-retention policies, DPA with sub-processors, EU-region storage option.
- **Input/Output:** validation via Form Requests, mass-assignment guarded, Blade auto-escaping, CSP + security headers (`SecurityHeaders` middleware: CSP, HSTS, X-Frame-Options, Referrer-Policy, Permissions-Policy), CSRF on web, sanitized rich text, file-upload validation (mime/size/av-scan), signed URLs for private media.
- **Abuse/fraud:** rate limiting, bot/spam protection on lead forms (honeypot + Turnstile/Cloudflare + optional score), WAF (Cloudflare), disposable-email detection, duplicate-lead detection, commission fraud checks (case verification workflow).
- **Secrets & infra:** `.env`/secret manager, no secrets in repo, least-privilege DB users, queue/redis auth, dependency scanning, automated security updates, audit logging of privileged actions, backups (encrypted, tested restores), staging/prod parity.
- **Payments:** PCI handled by Stripe/iyzico (no raw card data stored), webhook signature verification, idempotency.
- **Monitoring:** error tracking (Sentry/Flare), uptime, anomaly alerts, failed-job alerts, login-anomaly alerts.

## 6. Performance & scalability strategy

Targets: LCP < 2.0s, INP < 200ms, CLS < 0.1, TTFB < 300ms cached.

**Backend/data**
- **Redis** for cache, sessions, queues, locks. Cache heavy read pages (treatment/clinic/cost/country) with tag-based invalidation on content change; full-page/edge cache for anonymous marketing pages via Cloudflare.
- **Queues + Horizon** for all non-critical work (emails, AI, image processing, sitemaps, embeddings, notifications).
- Query discipline: eager loading (no N+1, enforced by Larastan/`preventLazyLoading` in non-prod), DB indexes ([05](05-database-schema-erd.md)), read-optimized denormalized counters (rating_avg/count, response_time), pagination (cursor), avoid heavy joins on hot paths — use Meilisearch for search/facets.
- **Meilisearch** for all discovery/search/faceting (fast, typo-tolerant) instead of SQL LIKE.
- OPcache + JIT (PHP 8.4), preloading, Octane (optional, Swoole/FrankenPHP) for high throughput later.

**Frontend/delivery**
- **Cloudflare CDN** + caching, Brotli, HTTP/2/3, edge cache marketing HTML for guests.
- **Images:** S3 + image CDN, AVIF/WebP, responsive `srcset`/`sizes`, explicit dimensions (no CLS), lazy-load below fold, LQIP/blur placeholders, strict upload optimization pipeline (`MediaService` generates variants async).
- **CSS/JS:** Tailwind purged, Vite build, code-split, defer non-critical JS, minimal Alpine, Livewire lazy-loading (`#[Lazy]`) for below-fold/heavy components (map, carousels, reviews), `wire:navigate` SPA-like nav with prefetch.
- **Fonts:** self-hosted, `font-display: swap`, preload primary weights, subset (latin + latin-ext + arabic later).
- **Critical CSS** inlined for above-the-fold; defer rest.
- Avoid third-party bloat; load chat/analytics/maps lazily and consent-gated.

**Scalability**
- Stateless app servers behind LB (sessions/cache in Redis) → horizontal scale.
- Read replica for MySQL when needed; queue workers scale independently; Meili + Redis as managed/replicated services.
- Media offloaded to S3/CDN (no local disk state). Autoscaling + health checks; graceful degradation (AI/search fallbacks).
- Observability: APM, slow-query log, cache hit-rate, queue depth, CWV RUM (real-user monitoring).

## 7. Dev workflow & quality
Pest tests (unit + feature + Dusk browser for critical flows: lead form, advisor, clinic onboarding), Larastan (static analysis, level ↑), Pint (style), Rector, CI pipeline (test + analyse + schema/schema-validate + link-check + Lighthouse budget), Envoyer/Deployer zero-downtime deploys, feature flags, seeded demo data, `/design-system` component gallery route.
