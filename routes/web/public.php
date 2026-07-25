<?php

declare(strict_types=1);

use App\Livewire\Public\AboutPage;
use App\Livewire\Public\AiCostEstimator;
use App\Livewire\Public\BeforeAfterIndex;
use App\Livewire\Public\BlogIndex;
use App\Livewire\Public\BlogShow;
use App\Livewire\Public\ClinicApplicationPage;
use App\Livewire\Public\ClinicShow;
use App\Livewire\Public\ClinicsIndex;
use App\Livewire\Public\ContactPage;
use App\Livewire\Public\CostShow;
use App\Livewire\Public\CountryShow;
use App\Livewire\Public\DoctorShow;
use App\Livewire\Public\DoctorsIndex;
use App\Livewire\Public\FaqPage;
use App\Livewire\Public\GdprPage;
use App\Livewire\Public\GetQuote;
use App\Livewire\Public\GuideArticleShow;
use App\Livewire\Public\GuidePillarPage;
use App\Livewire\Public\HomePage;
use App\Livewire\Public\HowItWorksPage;
use App\Livewire\Public\PrivacyPolicyPage;
use App\Livewire\Public\ReviewsIndex;
use App\Livewire\Public\ReviewsShow;
use App\Livewire\Public\TermsPage;
use App\Livewire\Public\TreatmentShow;
use App\Livewire\Public\TreatmentsIndex;
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
| Locale URL scheme (docs/06-seo-architecture.md §5): every locale lives
| under a required /{locale}/ path prefix (/en/..., /tr/...) — Laravel's
| official URL::defaults pattern. The locale param is provided by
| URL::defaults (set in SetLocale), so existing positional route() calls
| like route('treatments.show', $slug) keep working unchanged; the scalar
| fills the treatment, not the (defaulted) locale. This is why the prefix
| is REQUIRED, not optional {locale?} — an optional leading param gets
| consumed by positional args and breaks every route('x.show', $model)
| call. English isn't at the bare root as a result; `/` 301-redirects to
| the default locale's home, which is SEO-equivalent (canonical + x-default
| both target /en).
*/

// `/` -> the default locale's homepage (301). Everything else lives under
// a locale prefix, so this is the only unprefixed public entry point.
Route::get('/', fn () => redirect(route('home', ['locale' => config('clinicest.locales.default', 'en')]), 301));

Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('clinicest.locales.supported', ['en']))])
    ->group(function () {
        Route::get('/', HomePage::class)->name('home');

        Route::get('/treatments', TreatmentsIndex::class)->name('treatments.index');
        Route::get('/treatments/{treatment:slug}', TreatmentShow::class)->name('treatments.show');

        Route::get('/clinics', ClinicsIndex::class)->name('clinics.index');
        Route::get('/clinics/{clinic:slug}', ClinicShow::class)->name('clinics.show');

        Route::get('/doctors', DoctorsIndex::class)->name('doctors.index');
        Route::get('/doctors/{doctor:slug}', DoctorShow::class)->name('doctors.show');

        Route::get('/dental-tourism-turkey', GuidePillarPage::class)->name('guide.index');
        Route::get('/dental-tourism-turkey/{post:slug}', GuideArticleShow::class)->name('guide.show');

        Route::get('/countries/{country:slug}', CountryShow::class)->name('countries.show');

        Route::get('/cost/{treatment:slug}', CostShow::class)->name('cost.show');

        Route::get('/cost-estimator', AiCostEstimator::class)->name('cost-estimator');

        Route::get('/before-after', BeforeAfterIndex::class)->name('before-after.index');

        Route::get('/reviews', ReviewsIndex::class)->name('reviews.index');
        Route::get('/reviews/{clinic:slug}', ReviewsShow::class)->name('reviews.show');

        Route::get('/blog', BlogIndex::class)->name('blog.index');
        Route::get('/blog/{post:slug}', BlogShow::class)->name('blog.show');

        Route::get('/how-it-works', HowItWorksPage::class)->name('how-it-works');
        Route::get('/about', AboutPage::class)->name('about');
        Route::get('/contact', ContactPage::class)->name('contact');
        Route::get('/faq', FaqPage::class)->name('faq');

        Route::get('/legal/privacy', PrivacyPolicyPage::class)->name('legal.privacy');
        Route::get('/legal/terms', TermsPage::class)->name('legal.terms');
        Route::get('/legal/gdpr', GdprPage::class)->name('legal.gdpr');

        Route::get('/get-quote', GetQuote::class)->name('get-quote');

        // Guest-only: the form always creates a brand-new User + Clinic, so
        // an already-authenticated visitor isn't a supported entry point.
        Route::middleware('guest')->get('/for-clinics', ClinicApplicationPage::class)->name('for-clinics');
    });
