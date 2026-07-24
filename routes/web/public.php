<?php

declare(strict_types=1);

use App\Livewire\Public\GetQuote;
use App\Livewire\Public\HomePage;
use App\Livewire\Public\PlaceholderPage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public marketing routes
|--------------------------------------------------------------------------
| See docs/02-information-architecture-ux.md §1 for the full URL scheme
| and docs/04-wireframes.md for each page's spec. Pages not yet built out
| resolve to PlaceholderPage so every link in the layout is live.
|
| Note: titles below are plain English strings, not __() calls — route
| files load once at boot (and are frozen entirely under `route:cache`),
| before SetLocale has run for the request, so translating here would
| silently freeze every placeholder at the boot-time locale. Each of
| these gets a real, translated, SEO-driven title when it's built out.
|
| TODO (Phase 4, docs/10-roadmap.md): wire /{locale}/ prefix routing.
| Two registrations (default group as below, plus the same set wrapped in
| Route::prefix('{locale}')->where('locale', 'tr|de|...') with route names
| suffixed, e.g. 'treatments.index.tr') is the robust pattern — a single
| optional-prefix group is fragile around the root '/' route. Until then,
| config('clinicest.locales.supported') stays ['en'] so nothing 404s.
*/

Route::get('/', HomePage::class)->name('home');

Route::get('/treatments', PlaceholderPage::class)
    ->name('treatments.index')->defaults('title', 'Treatments');
Route::get('/treatments/{treatment:slug}', PlaceholderPage::class)
    ->name('treatments.show')->defaults('title', 'Treatment');

Route::get('/clinics', PlaceholderPage::class)
    ->name('clinics.index')->defaults('title', 'Clinics');
Route::get('/clinics/{clinic:slug}', PlaceholderPage::class)
    ->name('clinics.show')->defaults('title', 'Clinic');

Route::get('/doctors', PlaceholderPage::class)
    ->name('doctors.index')->defaults('title', 'Doctors');
Route::get('/doctors/{doctor:slug}', PlaceholderPage::class)
    ->name('doctors.show')->defaults('title', 'Doctor');

Route::get('/dental-tourism-turkey', PlaceholderPage::class)
    ->name('guide.index')->defaults('title', 'Dental Tourism Guide');

Route::get('/countries/{country:slug}', PlaceholderPage::class)
    ->name('countries.show')->defaults('title', 'Country');

Route::get('/cost/{treatment:slug}', PlaceholderPage::class)
    ->name('cost.show')->defaults('title', 'Treatment Cost');

Route::get('/before-after', PlaceholderPage::class)
    ->name('before-after.index')->defaults('title', 'Before & After');

Route::get('/reviews', PlaceholderPage::class)
    ->name('reviews.index')->defaults('title', 'Reviews');

Route::get('/blog', PlaceholderPage::class)
    ->name('blog.index')->defaults('title', 'Blog');

Route::get('/how-it-works', PlaceholderPage::class)
    ->name('how-it-works')->defaults('title', 'How It Works');
Route::get('/about', PlaceholderPage::class)
    ->name('about')->defaults('title', 'About');
Route::get('/contact', PlaceholderPage::class)
    ->name('contact')->defaults('title', 'Contact');
Route::get('/faq', PlaceholderPage::class)
    ->name('faq')->defaults('title', 'FAQ');

Route::get('/legal/privacy', PlaceholderPage::class)
    ->name('legal.privacy')->defaults('title', 'Privacy Policy');
Route::get('/legal/terms', PlaceholderPage::class)
    ->name('legal.terms')->defaults('title', 'Terms of Service');
Route::get('/legal/gdpr', PlaceholderPage::class)
    ->name('legal.gdpr')->defaults('title', 'GDPR');

Route::get('/get-quote', GetQuote::class)->name('get-quote');
