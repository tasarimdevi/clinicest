# Clinicest

[![CI](https://github.com/tasarimdevi/clinicest/actions/workflows/ci.yml/badge.svg)](https://github.com/tasarimdevi/clinicest/actions/workflows/ci.yml)

An independent dental-tourism marketplace — a neutral broker that captures
patient leads, routes them to verified clinics, and runs the partner CRM
behind it. Built on Laravel 12 + Livewire 4.

The product spec lives in [`docs/`](docs/) (start with
[`docs/00-README.md`](docs/00-README.md)); Clinicest is a broker, never a
clinic, and never fabricates reviews, badges, or before/after results.

## Tech stack

- **Laravel 12** (PHP 8.3) · **Livewire 4** · **Tailwind CSS v4** (Vite)
- **Spatie Permission** (roles/permissions) · **Spatie Translatable** (JSON-column i18n)
- **Laravel Scout** + Meilisearch (search) · **Sanctum** (auth)
- **Pest 3** (tests) · **Pint** (formatting)

## Local setup

Requires PHP 8.3, Composer, and Node 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
```

Then run the app (serve + queue + logs + Vite together):

```bash
composer dev
```

## Quality checks

The same two gates run in CI ([`.github/workflows/ci.yml`](.github/workflows/ci.yml))
on every push and PR to `main`. Run them locally before pushing:

```bash
php artisan test      # Pest suite (sqlite :memory:, no external services)
vendor/bin/pint       # auto-fix formatting  (--test to only check)
```

The test environment is self-contained — `phpunit.xml` pins sqlite, the
`collection` Scout driver, and array cache/mail — so no database or
Meilisearch instance is needed to run the suite.
