<?php

declare(strict_types=1);

it('renders the homepage in english by default', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Get Free Treatment Plan')
        ->assertDontSee('Ücretsiz Tedavi Planı Al');
});

it('switches to turkish and persists the choice in session for later requests', function () {
    $this->get(route('locale.switch', 'tr'))
        ->assertRedirect();

    expect(session('locale'))->toBe('tr');

    $this->get('/')
        ->assertOk()
        ->assertSee('Ücretsiz Tedavi Planı Al')
        ->assertSee('Onaylı klinik')
        ->assertDontSee('Get Free Treatment Plan');
});

it('translates static public pages once turkish is active', function () {
    $this->withSession(['locale' => 'tr'])
        ->get(route('about'))
        ->assertOk()
        ->assertSee('Clinicest Hakkında')
        ->assertSee('Misyonumuz');
});

it('rejects an unsupported locale', function () {
    $this->get(route('locale.switch', 'de'))->assertNotFound();

    expect(session('locale'))->not->toBe('de');
});

it('lets the language switcher links appear in the public layout', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(route('locale.switch', 'tr'), false)
        ->assertSee(route('locale.switch', 'en'), false);
});

it('renders every static public page in turkish without a raw untranslated dotted key leaking through', function () {
    $pages = [
        'treatments.index', 'clinics.index', 'doctors.index', 'reviews.index',
        'blog.index', 'guide.index', 'how-it-works', 'about', 'contact', 'faq',
        'legal.privacy', 'legal.terms', 'legal.gdpr', 'get-quote', 'before-after.index',
        'cost-estimator',
    ];

    foreach ($pages as $name) {
        $this->withSession(['locale' => 'tr'])
            ->get(route($name))
            ->assertOk()
            ->assertDontSee('home.hero_cta')
            ->assertDontSee('nav.treatments');
    }
});
