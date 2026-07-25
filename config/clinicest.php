<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Locales
    |--------------------------------------------------------------------------
    | English is served at the root (no prefix, canonical + x-default).
    | Additional locales are meant to live under a path prefix, e.g. /tr/...
    | (docs/06-seo-architecture.md §5, docs/08-laravel-architecture.md §3),
    | but that prefix routing is not wired yet — see the TODO in
    | routes/web/public.php. Translation files for 'tr' already exist under
    | lang/tr/ as prep, but only 'en' is listed as supported until the
    | locale-prefixed route group lands (docs/10-roadmap.md Phase 4).
    | SetLocale middleware still resolves session/Accept-Language today.
    */
    'locales' => [
        'default' => 'en',
        'supported' => ['en'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Target markets
    |--------------------------------------------------------------------------
    | See docs/01-product-strategy.md §6.
    */
    'markets' => [
        'primary' => ['GB', 'DE', 'IE'],
        'secondary' => ['US', 'CA', 'AU'],
        'future' => ['FR', 'NL', 'BE', 'SE', 'SA', 'AE'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Verification tiers
    |--------------------------------------------------------------------------
    | See docs/03-design-system.md §1 and app/Enums/VerificationTier.php.
    */
    'verification_tiers' => ['pending', 'verified', 'verified_plus', 'elite'],

    /*
    |--------------------------------------------------------------------------
    | Lead SLA
    |--------------------------------------------------------------------------
    | Hours a clinic has to respond to an assigned lead before it is
    | auto-reassigned. See docs/09-crm-admin-architecture.md §2.
    */
    'lead_sla_hours' => (int) env('LEAD_SLA_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Default commission rate
    |--------------------------------------------------------------------------
    | Percentage charged on completed treatment cases, absent a clinic- or
    | treatment-specific override. See docs/01-product-strategy.md §3.
    */
    'default_commission_rate' => (float) env('DEFAULT_COMMISSION_RATE', 12.5),

    /*
    |--------------------------------------------------------------------------
    | Contact inbox
    |--------------------------------------------------------------------------
    | Where /contact form submissions are sent. No ContactMessage model/admin
    | inbox yet (docs/10-roadmap.md doesn't scope one) — mail is the record.
    */
    'contact_email' => env('CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com')),

];
