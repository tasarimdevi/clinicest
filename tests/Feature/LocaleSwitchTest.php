<?php

declare(strict_types=1);

use App\Models\Treatment;

it('renders the default-locale page in english', function () {
    $this->get(route('home', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('Get Free Treatment Plan')
        ->assertDontSee('Ücretsiz Tedavi Planı Al');
});

it('renders a page under the turkish prefix in turkish', function () {
    $this->get(route('home', ['locale' => 'tr']))
        ->assertOk()
        ->assertSee('Ücretsiz Tedavi Planı Al')
        ->assertSee('Onaylı klinik')
        ->assertDontSee('Get Free Treatment Plan');
});

it('translates static public pages under the turkish prefix', function () {
    $this->get(route('about', ['locale' => 'tr']))
        ->assertOk()
        ->assertSee('Clinicest Hakkında')
        ->assertSee('Misyonumuz');
});

it('404s an unsupported locale prefix', function () {
    $this->get('/de/treatments')->assertNotFound();
});

it('shows language switcher links pointing at the per-locale URL of the current page', function () {
    $this->get(route('treatments.index', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('href="'.url('/en/treatments').'"', false)
        ->assertSee('href="'.url('/tr/treatments').'"', false);
});

it('emits a canonical and reciprocal hreflang alternates on a public page', function () {
    $this->get(route('treatments.index', ['locale' => 'en']))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.url('/en/treatments').'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/en/treatments').'">', false)
        ->assertSee('<link rel="alternate" hreflang="tr" href="'.url('/tr/treatments').'">', false)
        ->assertSee('<link rel="alternate" hreflang="x-default" href="'.url('/en/treatments').'">', false);
});

it('points the canonical at the turkish URL when viewing the turkish variant', function () {
    $this->get(route('treatments.index', ['locale' => 'tr']))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.url('/tr/treatments').'">', false)
        ->assertSee('<link rel="alternate" hreflang="tr" href="'.url('/tr/treatments').'">', false)
        ->assertSee('<link rel="alternate" hreflang="en" href="'.url('/en/treatments').'">', false);
});

it('keeps slug parameters intact in hreflang alternates', function () {
    Treatment::create(['slug' => 'dental-implants', 'name' => ['en' => 'Dental Implants'], 'status' => 'published']);

    $this->get(route('treatments.show', ['treatment' => 'dental-implants', 'locale' => 'en']))
        ->assertOk()
        ->assertSee('<link rel="alternate" hreflang="tr" href="'.url('/tr/treatments/dental-implants').'">', false);
});

it('renders every static public page under the turkish prefix without a leaked dotted key', function () {
    $pages = [
        'treatments.index', 'clinics.index', 'doctors.index', 'reviews.index',
        'blog.index', 'guide.index', 'how-it-works', 'about', 'contact', 'faq',
        'legal.privacy', 'legal.terms', 'legal.gdpr', 'get-quote', 'before-after.index',
        'cost-estimator',
    ];

    foreach ($pages as $name) {
        $this->get(route($name, ['locale' => 'tr']))
            ->assertOk()
            ->assertDontSee('home.hero_cta')
            ->assertDontSee('nav.treatments');
    }
});
