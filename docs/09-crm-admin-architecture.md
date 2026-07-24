# 09 · Roles, CRM, Clinic Dashboard & Admin

Three backend surfaces share one auth/permission system (Spatie): **Patient portal**, **Clinic dashboard**, **Admin/CRM**. All built with Livewire, same design system, dark-mode capable.

## 1. Roles & permissions

| Role | Scope | Key permissions |
|------|-------|-----------------|
| **Guest** | public | browse, submit lead, use advisor/chat |
| **Patient (lead)** | own data | created implicitly from a lead; magic-link/portal access to their request, offers, messages, appointments, review |
| **Registered Patient** | own account | full patient portal, saved clinics, multiple requests |
| **Clinic Owner** | own clinic(s) | manage profile, staff, leads, offers, subscription, billing, all clinic data |
| **Clinic Manager** | assigned clinic | leads, offers, messages, appointments, treatment plans, docs (no billing/staff) |
| **Clinic Staff** | assigned clinic | respond to leads/messages, appointments (limited) |
| **Doctor** | own profile + assigned cases | manage own profile content, view assigned cases/plans |
| **Sales/CRM Agent** | all leads | qualify, assign, note, follow-up, manage pipeline |
| **Content Editor** | content | posts, treatments copy, FAQs (draft/publish per permission) |
| **SEO Manager** | SEO | meta, redirects, keyword map, sitemaps, schema overrides, reports |
| **Moderator** | reviews/media | approve/reject reviews, before/after, clinic verification docs |
| **Finance** | billing | commissions, invoices, payments, subscriptions, reports |
| **Admin** | platform | all above except system-critical settings/roles |
| **Super Admin** | everything | roles/permissions, settings, integrations, danger zone |

Permissions are granular (`leads.view`, `leads.assign`, `clinics.verify`, `commissions.manage`, `content.publish`, `seo.manage`, …) and **clinic-scoped** for clinic roles via `clinic_user` + `EnsureClinicMember`. Every privileged action is audit-logged.

## 2. CRM architecture (internal sales/ops)

The CRM turns raw leads into completed, commissioned treatments. Core object: **Lead** → (assignment) → **Offer** → **Appointment** → **Treatment Case** → **Commission/Invoice**.

### Pipeline (kanban + table views)
```
New → Qualifying → Qualified → Assigned → Offer Sent → Negotiating → Won → (Treatment) → Completed
                                                            └→ Lost / Invalid
```

### Modules
- **Lead inbox / pipeline:** kanban by status + filterable table; lead score & quality (hot/warm/cold), source/UTM, treatment, country, budget; SLA timers (first-response, clinic-response); bulk actions.
- **Lead detail (360° view):** contact + treatment request, uploaded photos/x-rays, activity timeline (`lead_activities`), messages (web/WhatsApp/email unified), assignments, offers, appointments, consent record, internal notes, next-action/follow-up reminder.
- **Assignment & matching:** AI recommendation ([07](07-ai-architecture.md)) suggests clinics; agent assigns (1→N clinics) with SLA; auto-reassign on timeout; capacity/round-robin/quota rules by subscription tier.
- **Communication hub:** **WhatsApp Business API** integration (send/receive, templates, media), email (transactional + sequences), optional SMS; all threaded on the lead; canned responses & templates; multilingual.
- **Follow-up & automation:** rules engine (e.g. no response in 24h → reminder + reassign; offer viewed not accepted → nudge; abandoned lead form → recovery sequence); task/reminder system for agents.
- **Email automation:** consented sequences (welcome + what-happens-next, clinic-offer digest, pre-arrival checklist, post-treatment review request, reactivation). Templates in `email_templates`, per-locale.
- **Commission tracking:** on `treatment_case` completion → `CommissionService` generates commission (rate by tier/treatment) → status flow pending→invoiced→paid; disputes/waivers; reconciliation.
- **Invoices:** auto-generated PDFs (commission + subscription), numbered, tax handling, statuses, payment links, dunning for overdue.
- **Reports & analytics:** funnel conversion by stage/source/country/treatment/clinic, revenue (commission + subscription), agent performance, clinic performance (response time, win rate, review score), SLA compliance, cohort/retention, forecast. Exportable.

## 3. Clinic dashboard (partner SaaS)

Self-serve portal for partner clinics; access scoped to their clinic(s).

- **Overview:** KPIs — new leads, response-time vs SLA, win rate, revenue/commission owed, subscription status, profile completeness/verification tier, review score.
- **Lead inbox:** assigned leads with patient case (treatment, photos, budget, country), accept/decline, SLA countdown; only see assigned leads (privacy).
- **Patient messages:** threaded chat (web + WhatsApp bridge), attachments, templates, translation assist.
- **Appointment requests:** confirm/reschedule remote consults & on-site visits; calendar; reminders.
- **Offer / treatment-plan builder:** create structured offers (treatments, prices, includes: hotel/transfer/warranty, validity) → sent to patient + CRM; track viewed/accepted; versioned.
- **Documents:** upload/share treatment plans, x-ray responses, invoices, certificates; verification-doc uploads.
- **Commission reports:** cases sourced via Clinicest, commissions owed/paid, invoices, statements.
- **Subscription management:** plan, usage vs lead quota, upgrade/downgrade, billing history, payment method (Stripe/iyzico self-serve).
- **Profile editor:** gallery/videos, doctors, treatments & prices, technologies, languages, certificates, before/after (submit → moderation), respond to reviews. Changes to verified fields re-enter moderation.
- **Analytics:** lead volume/quality trends, conversion, review trends, profile views, ranking/placement insights (nudge to improve profile → better matching).

**Onboarding flow:** apply → upload license/credentials/ISO docs → admin verification workflow → profile build → choose subscription → go live (badge assigned). Verification tier gates visibility surfaces.

## 4. Admin panel

Full platform control, permission-gated, audited. Modules map to the brief:

- **Dashboard:** platform KPIs (leads, conversions, revenue, active clinics, CWV/SEO health, AI usage/cost), alerts, activity feed.
- **Clinics:** CRUD, verification workflow & tiers, featured toggles, pricing oversight, doc review, suspend.
- **Doctors:** CRUD, credential verification, featured, link to clinics.
- **Patients/Leads (CRM):** full CRM above; GDPR tools (export/erase), consent logs.
- **Treatments:** taxonomy, content, prices (base), FAQs, SEO, media, translations.
- **Countries/Cities:** target markets, currency, flight/visa info, country×treatment pSEO data, content.
- **Blog/Content:** posts, categories, editorial workflow (draft→review→schedule→publish), authors & medical reviewers, AI content assistant, FAQs.
- **Reviews:** moderation queue, verified-flag control, AI summaries, responses.
- **SEO:** meta editor, keyword map, redirects, sitemap control, schema overrides, hreflang oversight, orphan/thin-page reports, GSC/GA4 dashboards.
- **Media library:** central assets, variants, alt text, usage, cleanup.
- **Users & roles:** manage users, assign roles/permissions, clinic memberships, 2FA/status.
- **Payments/Commissions/Invoices:** finance ops, reconciliation, refunds, subscription oversight, reports.
- **Analytics:** cross-cutting BI (funnel, revenue, SEO, clinic/agent performance, AI cost/quality).
- **System settings:** site config, integrations (WhatsApp, Stripe/iyzico, AI providers, email, Meili, Cloudflare), feature flags, email templates, languages/locales, legal content, danger zone (Super Admin).

## 5. Notifications
Unified via Laravel notifications (mail, database, broadcast, WhatsApp channel): lead assigned, offer received/viewed/accepted, SLA breach, new message, appointment confirmed, commission due, subscription renewal/failure, verification status, review posted. User-configurable preferences; real-time in-app toasts + digest emails.

## 6. Analytics & reporting stack
Event tracking (product analytics + GA4/GSC), server-side aggregation into report tables/materialized views for dashboards (kept fast), scheduled report emails, exportable CSV/PDF, role-scoped visibility (clinics see only their data).
