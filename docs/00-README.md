# Clinicest.com — Product Blueprint

> Independent dental tourism marketplace connecting international patients with verified private dental clinics in Istanbul, Turkey.
> Business model: qualified lead generation + post-treatment commission + premium clinic subscriptions.

This `docs/` set is the single source of truth for design, product, and engineering. Read in order.

| # | Document | Covers (from the 24-item brief) |
|---|----------|----------------------------------|
| 01 | [Product Strategy](01-product-strategy.md) | 1. Product strategy, 6. User roles (overview), 24. Positioning |
| 02 | [Information Architecture & UX](02-information-architecture-ux.md) | 2. IA, 3. UX flows, 4. Sitemap, conversion system |
| 03 | [Design System](03-design-system.md) | 7. UI system, 8. Palette, 9. Typography, 10. Components |
| 04 | [Wireframes](04-wireframes.md) | 11. Homepage, 12. Every page, 13. Mobile-first |
| 05 | [Database Schema & ERD](05-database-schema-erd.md) | 5. Schema, 20. ERD |
| 06 | [SEO Architecture](06-seo-architecture.md) | 16. SEO, EEAT, schema, hreflang, programmatic/GEO |
| 07 | [AI Architecture](07-ai-architecture.md) | 17. AI features & services |
| 08 | [Laravel Architecture](08-laravel-architecture.md) | 18. App architecture, 19. Folders, 21. API, 22. Security, 23. Performance |
| 09 | [CRM, Clinic Dashboard & Admin](09-crm-admin-architecture.md) | 6. Roles, 14. CRM, 15. Admin |
| 10 | [Roadmap](10-roadmap.md) | 24. Future roadmap, delivery phases |

## Product one-liner

**Booking.com-grade trust × Airbnb-grade discovery × Healthgrades-grade medical authority — for dental treatment in Istanbul.**

## Core principles

1. **Trust is the product.** Verification, transparency, and safety signals appear on every screen. Nothing is faked.
2. **Every page is a lead engine.** One primary conversion goal: *Request a Free Consultation*. Everything else is a micro-conversion feeding it.
3. **SEO is the growth engine.** The information architecture is designed for programmatic scale (treatments × cities × countries × clinics × doctors) with clean topic clusters.
4. **Neutral marketplace.** Clinicest never poses as a clinic. It ranks, matches, and mediates — like Booking, not like a hotel.
5. **Mobile-first, performance-obsessed.** Patients research on phones at night. Core Web Vitals are a ranking and conversion lever, not an afterthought.

## Tech stack (locked)

Laravel 12 · PHP 8.4 · Livewire 3 + Volt · TailwindCSS · AlpineJS · MySQL 8 · Redis · Meilisearch · Cloudflare · S3 · REST API (versioned) · Spatie Permission · Spatie Translatable / route-based i18n.
